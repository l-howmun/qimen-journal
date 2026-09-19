<?php

declare(strict_types=1);

use App\Qimen\QimenEngine;
use Carbon\CarbonImmutable;

/*
|--------------------------------------------------------------------------
| Solar term coverage
|--------------------------------------------------------------------------
|
| The golden chart tests only ever exercise 白露 and 秋分, because that is when the
| reference screenshots were taken. Those two happen to be written identically in
| simplified and traditional Chinese, which hides a whole class of bug: the engine's
| lookup table and 6tail/lunar-php must agree on how every one of the 24 terms is
| spelled.
|
| They do not agree by default. lunar-php returns SIMPLIFIED (惊蛰, 谷雨, 小满, 芒种,
| 处暑) while the traditional forms (驚蟄, 穀雨, 小滿, 芒種, 處暑) are what most Qi Men
| literature prints. A table keyed on one and fed the other throws on roughly a fifth
| of the year while every golden test stays green.
|
| These tests exist so that can never ship.
|
*/

it('produces a valid chart for every day of a year', function (int $year): void {
    $at = CarbonImmutable::create($year, 1, 1, 12, 0, 0);
    $failures = [];

    for ($i = 0; $i < 365; $i++) {
        try {
            $chart = (new QimenEngine)->chartFor($at);

            if ($chart->ju < 1 || $chart->ju > 9) {
                $failures[] = $at->toDateString().': 局 out of range ('.$chart->ju.')';
            }
        } catch (Throwable $e) {
            $failures[] = $at->toDateString().': '.$e->getMessage();
        }

        $at = $at->addDay();
    }

    expect($failures)->toBe([], "failing days:\n".implode("\n", array_slice($failures, 0, 15)));
})->with([
    '2026' => [2026],
    '2027' => [2027],
]);

it('resolves all 24 solar terms across a year', function (): void {
    $at = CarbonImmutable::create(2026, 1, 1, 12, 0, 0);
    $seen = [];

    for ($i = 0; $i < 365; $i++) {
        $seen[(new QimenEngine)->chartFor($at)->solarTerm] = true;
        $at = $at->addDay();
    }

    // A single calendar year spans 23 or 24 distinct terms depending on where the
    // boundaries fall. Fewer than 23 means terms are being silently dropped.
    expect(count($seen))->toBeGreaterThanOrEqual(23);
});

it('handles the terms whose simplified and traditional forms differ', function (string $at, string $term): void {
    $chart = (new QimenEngine)->chartFor(CarbonImmutable::parse($at));

    expect($chart->solarTerm)->toBe($term)
        ->and($chart->ju)->toBeGreaterThanOrEqual(1)
        ->and($chart->ju)->toBeLessThanOrEqual(9);
})->with([
    '惊蛰 / 驚蟄' => ['2026-03-10 12:00', '惊蛰'],
    '谷雨 / 穀雨' => ['2026-04-25 12:00', '谷雨'],
    '小满 / 小滿' => ['2026-05-25 12:00', '小满'],
    '芒种 / 芒種' => ['2026-06-10 12:00', '芒种'],
    '处暑 / 處暑' => ['2026-08-25 12:00', '处暑'],
]);
