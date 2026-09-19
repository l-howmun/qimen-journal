<?php

declare(strict_types=1);

use App\Qimen\Enums\JuRule;
use App\Qimen\QimenEngine;
use Carbon\CarbonImmutable;

/*
|--------------------------------------------------------------------------
| 局數 determination
|--------------------------------------------------------------------------
|
| Picking the 局 number is the single most consequential rule in the engine, and
| the one where schools disagree. Everything downstream is a deterministic
| consequence of it, so a wrong 局 produces a chart that is wrong in all nine
| palaces while looking perfectly well formed.
|
| Project decision: follow kinqimen's 置閏法 exactly. See docs/qimen-spec.md.
|
*/

it('defaults to 置閏法', function (): void {
    expect((new QimenEngine)->juRule)->toBe(JuRule::ZhiRun);
});

it('matches the reference charts under 置閏法', function (string $at, int $ju): void {
    $chart = (new QimenEngine(JuRule::ZhiRun))->chartFor(CarbonImmutable::parse($at));

    expect($chart->ju)->toBe($ju);
})->with([
    'chart A' => ['2026-09-18 16:14', 7],
    'chart B' => ['2026-09-19 07:08', 7],
]);

it('produces a different, wrong 局 under 拆補法', function (string $at): void {
    $chart = (new QimenEngine(JuRule::ChaiBu))->chartFor(CarbonImmutable::parse($at));

    // 拆補法 stays on 白露 and yields 9. Both reference screenshots show 7.
    // This test exists to prove the rules genuinely differ and the flag is wired up,
    // not to bless 拆補法.
    expect($chart->ju)->toBe(9);
})->with([
    'chart A' => ['2026-09-18 16:14'],
    'chart B' => ['2026-09-19 07:08'],
]);

it('derives 元 from the day 符頭', function (string $at, string $yuan): void {
    $chart = (new QimenEngine)->chartFor(CarbonImmutable::parse($at));

    expect($chart->yuan->value)->toBe($yuan);
})->with([
    '乙未 → 符頭 甲午 → 上元' => ['2026-09-18 16:14', '上元'],
    '丙申 → 符頭 甲午 → 上元' => ['2026-09-19 07:08', '上元'],
    '己亥 is itself a 符頭 → 中元' => ['2026-09-22 12:00', '中元'],
]);

/*
|--------------------------------------------------------------------------
| 超神 behaviour across a 符頭 day and past the term boundary
|--------------------------------------------------------------------------
|
| All three datetimes below were run through kinqimen directly. These are its
| observed outputs, not a reading of its source.
|
|   date        day     拆補法   置閏法
|   2026-09-20  丁酉      9        7
|   2026-09-22  己亥      3        1     <- 符頭 day
|   2026-09-24  辛丑      1        1     <- after 秋分, methods converge
|
| Note that 2026-09-22 is a 符頭 day and 置閏法 still advances to 秋分 (局 1). There
| is no 符頭-day exception in its observable behaviour, so kinqimen and the classical
| rule in docs/qimen-spec.md agree. An earlier reading of the library source
| suggested otherwise and was wrong.
|
| 2026-09-24 is the first date where the two methods agree, because 秋分 arrives
| 2026-09-23 08:05 and the calendar catches up.
|
*/

it('advances past a 符頭 day while still in 超神', function (): void {
    $chart = (new QimenEngine)->chartFor(CarbonImmutable::parse('2026-09-22 12:00'));

    expect($chart->solarTerm)->toBe('白露')      // the sky
        ->and($chart->juSolarTerm)->toBe('秋分') // the 局 table
        ->and($chart->yuan->value)->toBe('中元')
        ->and($chart->ju)->toBe(1);
});

it('stops diverging from 拆補法 once the solar term catches up', function (): void {
    $at = CarbonImmutable::parse('2026-09-24 12:00');

    $zhiRun = (new QimenEngine(JuRule::ZhiRun))->chartFor($at);
    $chaiBu = (new QimenEngine(JuRule::ChaiBu))->chartFor($at);

    expect($zhiRun->solarTerm)->toBe('秋分')
        ->and($zhiRun->ju)->toBe(1)
        ->and($chaiBu->ju)->toBe($zhiRun->ju);
});

it('still separates the two methods on 2026-09-22', function (): void {
    $at = CarbonImmutable::parse('2026-09-22 12:00');

    // This is the discriminating date. A reference chart screenshot taken on 2026-09-22
    // showing 局 1 confirms 置閏法; showing 局 3 overturns everything.
    expect((new QimenEngine(JuRule::ChaiBu))->chartFor($at)->ju)->toBe(3)
        ->and((new QimenEngine(JuRule::ZhiRun))->chartFor($at)->ju)->toBe(1);
});

it('matches kinqimen on 2026-09-22', function (): void {
    // kinqimen 0.0.6.6 pan(2) returns 陰遁一局中元 for this datetime. Measured, not
    // inferred. As of 2026-09-19 kinqimen is the project's accepted oracle, so this
    // is a full assertion rather than a placeholder waiting on a screenshot.
    $chart = (new QimenEngine)->chartFor(CarbonImmutable::parse('2026-09-22 12:00'));

    expect($chart->ju)->toBe(1)
        ->and($chart->yuan->value)->toBe('中元')
        ->and($chart->dun->value)->toBe('陰遁');
});
