<?php

declare(strict_types=1);

use App\Qimen\QimenEngine;
use Carbon\CarbonImmutable;

/*
|--------------------------------------------------------------------------
| Oracle parity
|--------------------------------------------------------------------------
|
| Every row below is measured output from kinqimen 0.0.6.6 under pan(2) 置閏法,
| which this project accepts as the definition of a correct chart (see
| docs/qimen-spec.md). These are not derived, inferred or reasoned about. They
| were produced by running the library and copying what it printed.
|
| The golden chart fixtures only cover two datetimes, both 陰遁, both inside one
| 超神 stretch, both at ordinary times of day. This file widens that to the
| boundaries where the engine is most likely to be wrong: the 23:00 day rollover,
| the moment a solar term arrives, a 符頭 day, and 陽遁.
|
| Three of these rows currently fail. They are the defects found in review.
|
*/

dataset('oracle charts', [
    // datetime            day     hour    ju
    '22:30, before the day rolls over' => ['2026-09-16 22:30', '癸巳', '癸亥', 6],
    '23:30, after the day rolls over' => ['2026-09-16 23:30', '甲午', '甲子', 7],
    '00:30, same 時辰 as 23:30' => ['2026-09-17 00:30', '甲午', '甲子', 7],
    '符頭 day, still 超神' => ['2026-09-22 22:00', '己亥', '乙亥', 1],
    'midnight before 秋分 arrives' => ['2026-09-23 00:00', '庚子', '丙子', 3],
    'after 秋分 arrives' => ['2026-09-23 09:00', '庚子', '辛巳', 1],
    '陽遁, 符頭 day, 15 days into the term' => ['2026-05-05 12:00', '己卯', '庚午', 4],
    '陽遁 立春 中元' => ['2026-02-09 12:00', '甲寅', '庚午', 5],
    '陽遁, term advanced mid-cycle' => ['2026-02-14 12:00', '己未', '庚午', 3],
    '陽遁 雨水 上元' => ['2026-02-19 12:00', '甲子', '庚午', 9],
]);

it('matches the oracle day pillar', function (string $at, string $day): void {
    expect((new QimenEngine)->chartFor(CarbonImmutable::parse($at))->dayPillar)->toBe($day);
})->with('oracle charts');

it('matches the oracle hour pillar', function (string $at, string $day, string $hour): void {
    expect((new QimenEngine)->chartFor(CarbonImmutable::parse($at))->hourPillar)->toBe($hour);
})->with('oracle charts');

it('matches the oracle 局', function (string $at, string $day, string $hour, int $ju): void {
    expect((new QimenEngine)->chartFor(CarbonImmutable::parse($at))->ju)->toBe($ju);
})->with('oracle charts');
