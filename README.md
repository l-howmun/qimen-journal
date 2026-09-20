# qimen-journal

A Qi Men Dun Jia (奇門遁甲) hour chart engine in PHP, plus a journal for the readings
you take from it.

Give it a moment in time, get back the nine palaces: earth stems, heaven stems, the
nine stars, eight gates and eight deities, along with the 遁, 局, 元, 旬首, 值符 and 值使.

```php
use App\Qimen\QimenEngine;
use Carbon\CarbonImmutable;

$chart = (new QimenEngine)->chartFor(CarbonImmutable::parse('2026-09-19 07:08'));

$chart->dayPillar;              // 丙申
$chart->hourPillar;             // 壬辰
$chart->dun->value;             // 陰遁
$chart->ju;                     // 7
$chart->yuan->value;            // 上元
$chart->dutyStar->value;        // 天芮
$chart->dutyGate->value;        // 死門

$chart->palace(3)->earthStem->value;    // 壬
$chart->palace(3)->heavenStems;         // [癸, 庚]  — 天禽 rides with 天芮
$chart->palace(3)->stars;               // [天芮, 天禽]
$chart->palace(3)->gate->value;         // 休門
$chart->palace(3)->deity->value;        // 值符
```

## Status

| Part | State |
| :--- | :--- |
| Chart engine | working, 84 tests passing |
| MCP tools | not built yet |
| Reading journal | not built yet |
| Web UI | not planned |

The engine is the finished part. Everything else is ahead.

## Why this exists

Qi Men charts change every two hours, and reading one is a judgement call you make in
the moment. Most people take those readings, act on them, and never find out whether
they were any good.

The eventual point of this project is the boring half nobody does: write the reading
down, come back later, and mark whether it landed. The engine exists so that step
costs nothing.

## How correctness was established

This is the part worth reading, because Qi Men has schools that disagree and most
software does not say which one it implements.

Two hour charts from an established commercial Qi Men program were transcribed by
hand into fixtures, then independently reproduced by
[`kinqimen`](https://github.com/kentang2017/kinqimen) 0.0.6.6 under 置閏法. All 66
fields agree across both charts: nine earth stems, the heaven plate, nine stars,
eight gates, eight deities.

`kinqimen` is this project's accepted oracle. Where the two ever disagree, `kinqimen`
wins by definition.

Four rules had to be corrected against real charts rather than taken from research:

- **置閏法, not 拆補法.** A research pass reported 拆補法 with confident sourcing. It
  yields 局 9 where both reference charts plainly show 局 7.
- **Gate stepping** runs from palace 5 itself and diverts to palace 2 only on landing,
  not before stepping. One reference chart passed the naive rule by luck because its
  step count happened to be zero.
- **Plain wall clock time.** No longitude or true-solar correction, verified against a
  chart taken 8 minutes into a 時辰 where the two would differ.
- **The 符頭-day branch** lifts the 15-day cap on the 置閏 advance.

Full working, including the arithmetic for each reference chart, is in
[`docs/qimen-spec.md`](docs/qimen-spec.md).

## Three bugs a green test suite hid

Worth stating plainly, because it shaped how this repo is tested.

1. **A solar term table keyed on traditional characters** while `6tail/lunar-php`
   returns simplified. Five of 24 terms differ. It threw on 79 of 365 days. Both
   reference charts sit in 白露 and 秋分, the only two terms spelled identically in
   both scripts, so no fixture could ever reach it.
2. **The day pillar rolled at midnight** instead of 23:00, producing day and hour
   pillar pairs that cannot exist in the sexagenary cycle.
3. **The chart changed with the timezone** attached to the input `DateTime`. Same wall
   clock, different answer.

All three were found after the suite was green. The tests now include a two-year
daily sweep, a sexagenary validity invariant across every hour of several days, a
timezone invariance check, and oracle-parity cases chosen at the boundaries rather
than the comfortable middle.

## Requirements

- PHP 8.3+ (developed on 8.5)
- Composer

## Install

```sh
git clone https://github.com/l-howmun/qimen-journal.git
cd qimen-journal
composer install
cp .env.example .env
php artisan key:generate
php artisan migrate
```

## Tests

```sh
php artisan test          # 84 tests
./vendor/bin/pint --test  # style
```

Tests run against an in-memory SQLite database. No setup needed.

## What is not verified

Stated so nobody trusts this further than it has earned.

- **陽遁.** Both reference charts are 陰遁. Three rules reverse direction in 陽遁 and
  none has been checked against a real chart, only against the oracle.
- **接氣 periods and the 芒種/大雪 leap.** Every verified date sits in one 超神 stretch.
- **69 of 72 局 table cells**, copied from the 定局歌 and exercised only for internal
  consistency.
- **時辰 boundary behaviour** at an exact odd hour.

If you find a chart this engine gets wrong, please open an issue with the datetime and
what your software shows.

## Stack

Laravel 13, Livewire 4, Pest 4, `laravel/mcp`, and
[`6tail/lunar-php`](https://github.com/6tail/lunar-php) for 干支 pillars and 節氣
boundaries.

## A note on scope

This repository contains a calculation engine. It does not contain, reproduce or
redistribute any commercial product's charts, interpretations or artwork. The test
fixtures hold only numeric and symbolic chart values, which follow from classical
rules and were independently reproduced by an open-source implementation.

## License

MIT. See [LICENSE](LICENSE).
