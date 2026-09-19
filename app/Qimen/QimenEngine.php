<?php

declare(strict_types=1);

namespace App\Qimen;

use App\Qimen\Enums\Deity;
use App\Qimen\Enums\Dun;
use App\Qimen\Enums\Gate;
use App\Qimen\Enums\JuRule;
use App\Qimen\Enums\SolarTerm;
use App\Qimen\Enums\Star;
use App\Qimen\Enums\Stem;
use App\Qimen\Enums\Yuan;
use Carbon\CarbonImmutable;
use com\nlf\calendar\Solar;
use DateTimeInterface;

class QimenEngine
{
    /**
     * Heavenly stems in cyclic order.
     *
     * @var array<int, string>
     */
    private const array STEMS = ['甲', '乙', '丙', '丁', '戊', '己', '庚', '辛', '壬', '癸'];

    /**
     * Earthly branches in cyclic order.
     *
     * @var array<int, string>
     */
    private const array BRANCHES = ['子', '丑', '寅', '卯', '辰', '巳', '午', '未', '申', '酉', '戌', '亥'];

    /**
     * The 12 符頭 (talisman head) day pillars.
     *
     * @var array<int, string>
     */
    private const array FUTOU_DAYS = [
        '甲子', '甲午', '己卯', '己酉',
        '甲寅', '甲申', '己巳', '己亥',
        '甲辰', '甲戌', '己丑', '己未',
    ];

    /**
     * Outer 8 palaces in clockwise ring order.
     *
     * @var array<int, int>
     */
    private const array OUTER_RING = [1, 8, 3, 4, 9, 2, 7, 6];

    /**
     * Earth plate stem sequence starting at 戊.
     *
     * @var array<int, Stem>
     */
    private const array EARTH_STEM_SEQUENCE = [
        Stem::Wu,
        Stem::Ji,
        Stem::Geng,
        Stem::Xin,
        Stem::Ren,
        Stem::Gui,
        Stem::Ding,
        Stem::Bing,
        Stem::Yi,
    ];

    /**
     * Home star per palace (1..9).
     *
     * @var array<int, Star>
     */
    private const array HOME_STARS = [
        1 => Star::Peng,
        2 => Star::Rui,
        3 => Star::Chong,
        4 => Star::Fu,
        5 => Star::Qin,
        6 => Star::Xin,
        7 => Star::Zhu,
        8 => Star::Ren,
        9 => Star::Ying,
    ];

    /**
     * Home gate per palace (1..9).
     *
     * @var array<int, Gate>
     */
    private const array HOME_GATES = [
        1 => Gate::Xiu,
        2 => Gate::Si,
        3 => Gate::Shang,
        4 => Gate::Du,
        5 => Gate::Si,
        6 => Gate::Kai,
        7 => Gate::JingShock,
        8 => Gate::Sheng,
        9 => Gate::Jing,
    ];

    /**
     * Gate cycle order.
     *
     * @var array<int, Gate>
     */
    private const array GATE_CYCLE = [
        Gate::Xiu,
        Gate::Sheng,
        Gate::Shang,
        Gate::Du,
        Gate::Jing,
        Gate::Si,
        Gate::JingShock,
        Gate::Kai,
    ];

    /**
     * Deities cycle order.
     *
     * @var array<int, Deity>
     */
    private const array DEITIES_CYCLE = [
        Deity::Chief,
        Deity::Snake,
        Deity::Moon,
        Deity::Harmony,
        Deity::Tiger,
        Deity::Tortoise,
        Deity::Earth,
        Deity::Heaven,
    ];

    public function __construct(
        public readonly JuRule $juRule = JuRule::ZhiRun,
    ) {}

    /**
     * Generate a Qi Men Dun Jia hour chart for the given local datetime.
     */
    public function chartFor(DateTimeInterface $dateTime): Chart
    {
        $year = (int) $dateTime->format('Y');
        $month = (int) $dateTime->format('n');
        $day = (int) $dateTime->format('j');
        $hour = (int) $dateTime->format('G');
        $minute = (int) $dateTime->format('i');
        $second = (int) $dateTime->format('s');

        $solar = Solar::fromYmdHms($year, $month, $day, $hour, $minute, $second);
        $lunar = $solar->getLunar();

        $dayPillar = $lunar->getDayInGanZhiExact();
        $hourPillar = $lunar->getTimeInGanZhi();

        $solarTerm = SolarTerm::fromName($lunar->getPrevJieQi()->getName());
        $juSolarTerm = $solarTerm;

        if ($this->juRule === JuRule::ZhiRun) {
            $prevSolar = $lunar->getPrevJieQi()->getSolar();
            $prevJieQiDate = CarbonImmutable::create(
                $prevSolar->getYear(),
                $prevSolar->getMonth(),
                $prevSolar->getDay(),
                $prevSolar->getHour(),
                $prevSolar->getMinute(),
                $prevSolar->getSecond(),
                'UTC'
            );

            $currentDate = CarbonImmutable::create($year, $month, $day, $hour, $minute, $second, 'UTC');
            $difference = (int) $prevJieQiDate->diffInDays($currentDate);

            $isFutouDay = in_array($dayPillar, self::FUTOU_DAYS, true);
            $shouldAdvance = $isFutouDay
                ? $difference >= 9
                : ($difference >= 9 && $difference < 15);

            if ($shouldAdvance) {
                $juSolarTerm = SolarTerm::fromName($lunar->getNextJieQi()->getName());
            }
        }

        [$dun, $shangJu, $zhongJu, $xiaJu] = $juSolarTerm->juInfo();

        $dayStemChar = mb_substr($dayPillar, 0, 1);
        $dayBranchChar = mb_substr($dayPillar, 1, 1);

        $dayStemIdx = (int) array_search($dayStemChar, self::STEMS, true);
        $dayBranchIdx = (int) array_search($dayBranchChar, self::BRANCHES, true);

        $offset = $dayStemIdx % 5;
        $futouBranch = ($dayBranchIdx - $offset + 12) % 12;

        $yuan = match ($futouBranch) {
            0, 3, 6, 9 => Yuan::Shang,
            2, 5, 8, 11 => Yuan::Zhong,
            1, 4, 7, 10 => Yuan::Xia,
        };

        $ju = match ($yuan) {
            Yuan::Shang => $shangJu,
            Yuan::Zhong => $zhongJu,
            Yuan::Xia => $xiaJu,
        };

        /** @var array<int, Stem> $earthStems */
        $earthStems = [];
        for ($k = 0; $k < 9; $k++) {
            if ($dun === Dun::Yang) {
                $p = (($ju - 1 + $k) % 9) + 1;
            } else {
                $p = ((($ju - 1 - $k) % 9 + 9) % 9) + 1;
            }
            $earthStems[$p] = self::EARTH_STEM_SEQUENCE[$k];
        }

        $hourStemChar = mb_substr($hourPillar, 0, 1);
        $hourBranchChar = mb_substr($hourPillar, 1, 1);

        $hourStemIdx = (int) array_search($hourStemChar, self::STEMS, true);
        $hourBranchIdx = (int) array_search($hourBranchChar, self::BRANCHES, true);

        $xunBranch = ($hourBranchIdx - $hourStemIdx + 12) % 12;

        [$xunShou, $hiddenInstrument] = match ($xunBranch) {
            0 => ['甲子', Stem::Wu],
            10 => ['甲戌', Stem::Ji],
            8 => ['甲申', Stem::Geng],
            6 => ['甲午', Stem::Xin],
            4 => ['甲辰', Stem::Ren],
            2 => ['甲寅', Stem::Gui],
        };

        $pXun = 0;
        foreach ($earthStems as $p => $stem) {
            if ($stem === $hiddenInstrument) {
                $pXun = $p;
                break;
            }
        }

        $dutyStar = ($pXun === 5) ? Star::Rui : self::HOME_STARS[$pXun];
        $dutyGate = ($pXun === 5) ? Gate::Si : self::HOME_GATES[$pXun];

        if ($hourStemChar === '甲') {
            $pTarget = ($pXun === 5) ? 2 : $pXun;
        } else {
            $hourStemEnum = Stem::from($hourStemChar);
            $pHourStem = 0;
            foreach ($earthStems as $p => $stem) {
                if ($stem === $hourStemEnum) {
                    $pHourStem = $p;
                    break;
                }
            }
            $pTarget = ($pHourStem === 5) ? 2 : $pHourStem;
        }

        $pHome = ($pXun === 5) ? 2 : $pXun;
        $targetRingIdx = (int) array_search($pTarget, self::OUTER_RING, true);
        $homeRingIdx = (int) array_search($pHome, self::OUTER_RING, true);
        $rotationOffset = ($targetRingIdx - $homeRingIdx + 8) % 8;

        $isFuYin = ($rotationOffset === 0);

        $palaceStars = [];
        $palaceHeavenStems = [];

        $palaceStars[5] = [];
        $palaceHeavenStems[5] = [$earthStems[5]];

        for ($i = 0; $i < 8; $i++) {
            $destPalace = self::OUTER_RING[$i];
            $sourceRingIdx = ($i - $rotationOffset + 8) % 8;
            $sourcePalace = self::OUTER_RING[$sourceRingIdx];

            if ($sourcePalace === 2) {
                $palaceStars[$destPalace] = [Star::Rui, Star::Qin];
                $palaceHeavenStems[$destPalace] = $isFuYin
                    ? [$earthStems[2]]
                    : [$earthStems[2], $earthStems[5]];
            } else {
                $palaceStars[$destPalace] = [self::HOME_STARS[$sourcePalace]];
                $palaceHeavenStems[$destPalace] = [$earthStems[$sourcePalace]];
            }
        }

        $delta = $hourStemIdx;
        if ($dun === Dun::Yang) {
            $pRaw = (($pXun - 1 + $delta) % 9) + 1;
        } else {
            $pRaw = ((($pXun - 1 - $delta) % 9 + 9) % 9) + 1;
        }
        $pDest = ($pRaw === 5) ? 2 : $pRaw;

        $dutyGateIdx = (int) array_search($dutyGate, self::GATE_CYCLE, true);
        $destRingIdx = (int) array_search($pDest, self::OUTER_RING, true);

        $palaceGates = [];
        $palaceGates[5] = null;

        for ($i = 0; $i < 8; $i++) {
            $palace = self::OUTER_RING[$i];
            $steps = ($i - $destRingIdx + 8) % 8;
            $gateIdx = ($dutyGateIdx + $steps) % 8;
            $palaceGates[$palace] = self::GATE_CYCLE[$gateIdx];
        }

        $palaceDeities = [];
        $palaceDeities[5] = null;

        for ($i = 0; $i < 8; $i++) {
            $palace = self::OUTER_RING[$i];
            if ($dun === Dun::Yang) {
                $deityIdx = ($i - $targetRingIdx + 8) % 8;
            } else {
                $deityIdx = ($targetRingIdx - $i + 8) % 8;
            }
            $palaceDeities[$palace] = self::DEITIES_CYCLE[$deityIdx];
        }

        $palaces = [];
        for ($n = 1; $n <= 9; $n++) {
            $palaces[$n] = new Palace(
                number: $n,
                earthStem: $earthStems[$n],
                heavenStems: $palaceHeavenStems[$n],
                stars: $palaceStars[$n],
                gate: $palaceGates[$n],
                deity: $palaceDeities[$n],
            );
        }

        return new Chart(
            dayPillar: $dayPillar,
            hourPillar: $hourPillar,
            solarTerm: $solarTerm->value,
            juSolarTerm: $juSolarTerm->value,
            dun: $dun,
            ju: $ju,
            yuan: $yuan,
            xunShou: $xunShou,
            dutyStar: $dutyStar,
            dutyGate: $dutyGate,
            isFuYin: $isFuYin,
            palaces: $palaces,
        );
    }
}
