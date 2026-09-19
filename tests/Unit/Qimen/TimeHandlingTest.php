<?php

declare(strict_types=1);

use App\Qimen\QimenEngine;
use Carbon\CarbonImmutable;

/*
|--------------------------------------------------------------------------
| Time handling invariants
|--------------------------------------------------------------------------
|
| Two properties that must hold for every input, not just the ones we happen to
| have oracle rows for. Both were violated by the first implementation.
|
| 1. The day and hour pillars must be a pair that can actually occur. The Chinese
|    day rolls over at 23:00, not at midnight, and lunar-php reports the two
|    halves inconsistently: getTimeInGanZhi() already uses tomorrow's day stem
|    while getDayInGanZhi() is still on today's. Trusting both produces pairs
|    that do not exist in the 60 JiaZi cycle.
|
| 2. The chart depends on wall clock time only. Spec Section 10 establishes that
|    no longitude or solar-time correction is applied, so the timezone attached
|    to a DateTime must not change the answer.
|
*/

/**
 * The hour stem is fully determined by the day stem and the hour branch:
 * hourStem = ((dayStem mod 5) * 2 + hourBranch) mod 10
 *
 * Any other combination cannot occur in the sexagenary cycle.
 */
function isPossiblePillarPair(string $dayPillar, string $hourPillar): bool
{
    $stems = ['甲', '乙', '丙', '丁', '戊', '己', '庚', '辛', '壬', '癸'];
    $branches = ['子', '丑', '寅', '卯', '辰', '巳', '午', '未', '申', '酉', '戌', '亥'];

    $dayStem = array_search(mb_substr($dayPillar, 0, 1), $stems, true);
    $hourStem = array_search(mb_substr($hourPillar, 0, 1), $stems, true);
    $hourBranch = array_search(mb_substr($hourPillar, 1, 1), $branches, true);

    if ($dayStem === false || $hourStem === false || $hourBranch === false) {
        return false;
    }

    return ((($dayStem % 5) * 2) + $hourBranch) % 10 === $hourStem;
}

it('never produces a day and hour pillar pair that cannot exist', function (string $date): void {
    $impossible = [];

    for ($hour = 0; $hour < 24; $hour++) {
        foreach ([1, 31] as $minute) {
            $at = CarbonImmutable::parse($date)->setTime($hour, $minute);
            $chart = (new QimenEngine)->chartFor($at);

            if (! isPossiblePillarPair($chart->dayPillar, $chart->hourPillar)) {
                $impossible[] = $at->format('Y-m-d H:i').' → '.$chart->dayPillar.' / '.$chart->hourPillar;
            }
        }
    }

    expect($impossible)->toBe([], "impossible pairs:\n".implode("\n", $impossible));
})->with([
    'a 符頭 day' => ['2026-09-17'],
    'an ordinary day' => ['2026-09-19'],
    'a day a solar term arrives' => ['2026-09-23'],
    'a 陽遁 day' => ['2026-02-14'],
]);

it('rolls the day pillar over at 23:00, not at midnight', function (): void {
    $before = (new QimenEngine)->chartFor(CarbonImmutable::parse('2026-09-16 22:59'));
    $after = (new QimenEngine)->chartFor(CarbonImmutable::parse('2026-09-16 23:01'));

    expect($before->dayPillar)->toBe('癸巳')
        ->and($after->dayPillar)->toBe('甲午');
});

it('gives the same chart regardless of the timezone attached to the input', function (string $wallClock): void {
    $charts = [];

    foreach (['UTC', 'Asia/Kuala_Lumpur', 'America/New_York', 'Pacific/Kiritimati'] as $timezone) {
        $at = new DateTimeImmutable($wallClock, new DateTimeZone($timezone));
        $chart = (new QimenEngine)->chartFor($at);

        $charts[$timezone] = [
            'day' => $chart->dayPillar,
            'hour' => $chart->hourPillar,
            'ju' => $chart->ju,
            'solarTerm' => $chart->solarTerm,
            'juSolarTerm' => $chart->juSolarTerm,
        ];
    }

    $distinct = array_unique(array_map(fn (array $c): string => implode('|', $c), $charts));

    expect($distinct)->toHaveCount(1, 'charts differ by timezone: '.json_encode($charts, JSON_UNESCAPED_UNICODE));
})->with([
    'mid-afternoon' => ['2026-09-18 16:14:00'],
    'early morning' => ['2026-09-19 07:08:00'],
    'near the 超神 threshold' => ['2026-09-17 04:00:00'],
    'just after the day rolls over' => ['2026-09-16 23:30:00'],
]);
