<?php

declare(strict_types=1);

use App\Qimen\QimenEngine;
use Carbon\CarbonImmutable;

/*
|--------------------------------------------------------------------------
| Golden chart tests
|--------------------------------------------------------------------------
|
| These are the acceptance tests for the engine. Each fixture is a reference
| chart, transcribed from a screenshot and independently reproduced by kinqimen.
| The engine is done when every assertion here passes.
|
| Do not edit a fixture to make a test pass. The fixtures are ground truth.
|
*/

dataset('reference charts', [
    'chart A — 伏吟, hour stem 甲, plate does not rotate' => ['chart-a'],
    'chart B — hour stem 壬, plate rotates' => ['chart-b'],
]);

it('derives the correct pillars and solar term', function (string $fixture): void {
    $expected = qimenFixture($fixture);
    $chart = (new QimenEngine)->chartFor(CarbonImmutable::parse($expected['at']));

    expect($chart->dayPillar)->toBe($expected['dayPillar'])
        ->and($chart->hourPillar)->toBe($expected['hourPillar'])
        ->and($chart->solarTerm)->toBe($expected['solarTerm']);
})->with('reference charts');

it('reports which solar term the 局 was looked up under', function (string $fixture): void {
    $expected = qimenFixture($fixture);
    $chart = (new QimenEngine)->chartFor(CarbonImmutable::parse($expected['at']));

    // Under 超神 these differ: the sky says 白露, the 局 table is read under 秋分.
    // Exposing both makes the 置閏 mechanism observable instead of a hidden jump.
    expect($chart->juSolarTerm)->toBe($expected['juSolarTerm'])
        ->and($chart->juSolarTerm)->not->toBe($chart->solarTerm);
})->with('reference charts');

it('derives the correct 遁, 局 and 元', function (string $fixture): void {
    $expected = qimenFixture($fixture);
    $chart = (new QimenEngine)->chartFor(CarbonImmutable::parse($expected['at']));

    expect($chart->dun->value)->toBe($expected['dun'])
        ->and($chart->ju)->toBe($expected['ju'])
        ->and($chart->yuan->value)->toBe($expected['yuan']);
})->with('reference charts');

it('derives the correct 旬首, 值符 and 值使', function (string $fixture): void {
    $expected = qimenFixture($fixture);
    $chart = (new QimenEngine)->chartFor(CarbonImmutable::parse($expected['at']));

    expect($chart->xunShou)->toBe($expected['xunShou'])
        ->and($chart->dutyStar->value)->toBe($expected['dutyStar'])
        ->and($chart->dutyGate->value)->toBe($expected['dutyGate']);
})->with('reference charts');

it('places every earth plate stem correctly', function (string $fixture): void {
    $expected = qimenFixture($fixture);
    $chart = (new QimenEngine)->chartFor(CarbonImmutable::parse($expected['at']));

    foreach ($expected['palaces'] as $number => $palace) {
        expect($chart->palace((int) $number)->earthStem->value)
            ->toBe($palace['earthStem'], "earth stem in palace {$number}");
    }
})->with('reference charts');

it('places every heaven plate stem correctly', function (string $fixture): void {
    $expected = qimenFixture($fixture);
    $chart = (new QimenEngine)->chartFor(CarbonImmutable::parse($expected['at']));

    foreach ($expected['palaces'] as $number => $palace) {
        expect(enumValues($chart->palace((int) $number)->heavenStems))
            ->toBe($palace['heavenStems'], "heaven stems in palace {$number}");
    }
})->with('reference charts');

it('places every star correctly', function (string $fixture): void {
    $expected = qimenFixture($fixture);
    $chart = (new QimenEngine)->chartFor(CarbonImmutable::parse($expected['at']));

    foreach ($expected['palaces'] as $number => $palace) {
        expect(enumValues($chart->palace((int) $number)->stars))
            ->toBe($palace['stars'], "stars in palace {$number}");
    }
})->with('reference charts');

it('places every gate correctly', function (string $fixture): void {
    $expected = qimenFixture($fixture);
    $chart = (new QimenEngine)->chartFor(CarbonImmutable::parse($expected['at']));

    foreach ($expected['palaces'] as $number => $palace) {
        expect($chart->palace((int) $number)->gate?->value)
            ->toBe($palace['gate'], "gate in palace {$number}");
    }
})->with('reference charts');

it('places every deity correctly', function (string $fixture): void {
    $expected = qimenFixture($fixture);
    $chart = (new QimenEngine)->chartFor(CarbonImmutable::parse($expected['at']));

    foreach ($expected['palaces'] as $number => $palace) {
        expect($chart->palace((int) $number)->deity?->value)
            ->toBe($palace['deity'], "deity in palace {$number}");
    }
})->with('reference charts');

it('leaves palace 5 without a gate or deity', function (string $fixture): void {
    $expected = qimenFixture($fixture);
    $chart = (new QimenEngine)->chartFor(CarbonImmutable::parse($expected['at']));

    expect($chart->palace(5)->gate)->toBeNull()
        ->and($chart->palace(5)->deity)->toBeNull()
        ->and($chart->palace(5)->stars)->toBe([]);
})->with('reference charts');

it('flags 伏吟 only when the heaven plate matches the earth plate', function (string $fixture): void {
    $expected = qimenFixture($fixture);
    $chart = (new QimenEngine)->chartFor(CarbonImmutable::parse($expected['at']));

    expect($chart->isFuYin)->toBe($expected['fuYin']);
})->with('reference charts');

it('rejects an out of range palace number', function (): void {
    $chart = (new QimenEngine)->chartFor(CarbonImmutable::parse('2026-09-18 16:14'));

    $chart->palace(10);
})->throws(InvalidArgumentException::class);
