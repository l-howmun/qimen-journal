# Station 2 handoff brief — Qi Men chart engine

## 1. Ticket and definition of done

**Ticket:** QMJ-1 — implement the Qi Men Dun Jia hour chart engine.

**Done means:** every test under `tests/Unit/Qimen/` passes, and both

```sh
./vendor/bin/pint --test
php artisan test
```

exit 0. Nothing else counts as done. A passing description is not a passing test.

## 2. Acceptance tests (already written — do not edit them)

| File | What it pins |
| :--- | :--- |
| `tests/Unit/Qimen/ChartGenerationTest.php` | full chart output against two real reference charts |
| `tests/Unit/Qimen/JuDeterminationTest.php` | the 局數 rule, both methods, the 符頭-day exception |
| `tests/Fixtures/qimen/chart-a.json` | reference chart, 伏吟 case |
| `tests/Fixtures/qimen/chart-b.json` | reference chart, rotating case |
| `tests/Pest.php` | `qimenFixture()` and `enumValues()` helpers |

**The fixtures are ground truth.** They were transcribed from reference software hour
charts and independently reproduced by the Python library `kinqimen`, 66 of 66 fields. If your code disagrees with a fixture, your code is wrong. Never
edit a fixture or a test to make something pass. If you believe a fixture is wrong,
stop and say so rather than changing it.

One test is intentionally skipped (`matches a real reference chart on a 符頭 day`).
Leave it skipped. It is waiting on a screenshot that does not exist yet.

## 3. The specification

Read `docs/qimen-spec.md` in full before writing any code. Every rule is marked
verified or unverified. Pay particular attention to:

- **Section 2** — the 局數 rule. Follow 置閏法, matching `kinqimen` exactly, including
  its 符頭-day exception. This is a project decision, recorded in the spec.
- **Section 7** — the gate stepping rule contains a **correction**. The naive version
  is wrong and passes chart A by luck. Read the callout box.
- **Section 9** — palace 5 handling (寄坤二宮). Both charts exercise it.

## 4. Suggested shape (not binding)

The tests already pin the public API. Anything satisfying it is acceptable.

```
app/Qimen/QimenEngine.php          chartFor(DateTimeInterface): Chart
app/Qimen/Chart.php                readonly value object, palace(int $n): Palace
app/Qimen/Palace.php               readonly value object
app/Qimen/Enums/Stem.php           backed by the Chinese character
app/Qimen/Enums/Star.php
app/Qimen/Enums/Gate.php
app/Qimen/Enums/Deity.php
app/Qimen/Enums/Dun.php            陽遁 / 陰遁
app/Qimen/Enums/Yuan.php           上元 / 中元 / 下元
app/Qimen/Enums/JuRule.php         拆補法 / 置閏法
```

`QimenEngine::__construct(JuRule $juRule = JuRule::ZhiRun)` and the property must be
publicly readable as `$engine->juRule`.

`Chart` exposes **two** solar term fields and they are not the same thing:

- `$chart->solarTerm` — the astronomical term the moment actually falls in (白露).
- `$chart->juSolarTerm` — the term whose row was used for the 局 lookup (秋分).

Under 超神 these differ. Both reference charts differ. Do not collapse them into one
field; the tests assert they are different.

`Chart::palace()` must throw `InvalidArgumentException` outside 1..9.

Enum backing values are the Chinese characters exactly as they appear in the
fixtures. `天芮` not `芮`, `死門` not `死`.

## 5. Use the installed calendar library

`6tail/lunar-php` is already installed. Use it for day pillar, hour pillar and solar
term boundaries. Do not hand-roll calendar maths.

```php
use com\nlf\calendar\Solar;

$lunar = Solar::fromYmdHms(2026, 9, 18, 16, 14, 0)->getLunar();
$lunar->getDayInGanZhi();            // 乙未
$lunar->getTimeInGanZhi();           // 甲申
$lunar->getPrevJieQi()->getName();   // 白露
$lunar->getPrevJieQi()->getSolar();  // 2026-09-07 22:41:16
```

Feed it plain local clock time. Do not apply any longitude or solar-time correction;
spec Section 10 verifies that the reference software does not.

## 6. Conventions

Follow `CLAUDE.md` in the project root (Laravel Boost guidelines). In particular:
strict types, constructor property promotion, explicit return types and parameter
type hints, PHPDoc array shapes, TitleCase enum case names, curly braces always.

Run `vendor/bin/pint --dirty` after editing PHP.

## 7. Branch

Work on `feat/qimen-engine`. It already exists and is checked out.

## 8. LAW 2 — verification

You are not finished until, from the project root:

```sh
./vendor/bin/pint --test     # must exit 0
php artisan test             # must exit 0
```

If tests fail, read the stack trace, make a targeted fix, and re-run. Repeat until
green. Do not disable, skip or delete a test to get there.

## 9. LAW 1 — absolute prohibition on git remote operations

**Do not run `git push`, `git remote add`, `git remote set-url`, `git clone`, or any
other command that contacts a remote.** Local commits only.

The default `github.com` SSH host on this machine belongs to the user's work account
and must never be touched by this project. Shipping is Station 4 and is handled by
the orchestrator, not by you.

If you think a task requires pushing, it does not. Stop and report instead.
