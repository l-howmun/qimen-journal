# Qi Men Dun Jia Hour Chart — Engine Specification

**Status:** Station 1 blueprint. Verified against 1 reference chart.
**Target:** reproduce hour charts from the reference software (時家奇門, 轉盤 rotational method) exactly.

---

## How this document was produced

Two research passes were run. They disagreed on the 局數 method, which is the
single most consequential rule in the engine. The disagreement was settled by
checking both candidates against a real reference chart
(reference chart 1, below). See [Section 2](#2-局數-ju-number-1-9).

Everything in this spec that is marked **verified** reproduces that chart exactly.
Everything marked **unverified** has not yet been tested against anything and must
not be trusted.

---

## Reference chart 1

| Field | Value | Source |
| :--- | :--- | :--- |
| Date/time | 2026-09-18 16:14 | chart screenshot |
| Day pillar | 乙未 | `6tail/lunar-php` |
| Hour pillar | 甲申 | `6tail/lunar-php` |
| Previous 節氣 | 白露 @ 2026-09-07 22:41:16 | `6tail/lunar-php` |
| Next 節氣 | 秋分 @ 2026-09-23 08:05:14 | `6tail/lunar-php` |
| Chart 局 | **陰遁七局** | derived from the chart's earth plate stems |

The 局 was read off the chart rather than assumed: the earth-plate stems run
戊(7) 己(6) 庚(5) 辛(4) 壬(3) 癸(2) 丁(1) 丙(9) 乙(8), i.e. starting at palace 7
and travelling backward. That is 陰遁, 局 7, with no ambiguity.

> **Useful shortcut:** the number the reference software prints in the centre palace equals the
> 局數. Here it prints 7. Every future reference screenshot therefore states its
> own 局 directly, which makes golden tests cheap to write.

---

## Reference chart 2

| Field | Value | Source |
| :--- | :--- | :--- |
| Date/time | 2026-09-19 07:08 | chart screenshot |
| Day pillar | 丙申 | `6tail/lunar-php` |
| Hour pillar | 壬辰 | `6tail/lunar-php` |
| Previous 節氣 | 白露 @ 2026-09-07 22:41:16 | `6tail/lunar-php` |
| Next 節氣 | 秋分 @ 2026-09-23 08:05:14 | `6tail/lunar-php` |
| Chart 局 | **陰遁七局** | earth plate stems, identical layout to chart 1 |

This chart is far more informative than chart 1 because the **hour stem is 壬, not 甲**.
The heaven plate therefore actually rotates, which chart 1 could not test.

It also settles the local-solar-time question. See [Section 10](#10-time-basis).

---

## 1. 陽遁 vs 陰遁

**Verified.**

- 陽遁 runs from 冬至 (winter solstice, apparent solar longitude 270°) through 芒種.
- 陰遁 runs from 夏至 (summer solstice, 90°) through 大雪.

The switch happens at the astronomical instant of the solstice, not at midnight.

Chart 1 falls in 白露/秋分 territory, which is 陰遁. The chart is 陰遁. ✅

---

## 2. 局數 (ju number, 1-9)

### ⚠️ Character set

The table below prints **traditional** forms, as Qi Men literature does.
`6tail/lunar-php` returns **simplified**. Five terms differ:

| Traditional (this doc) | Simplified (what the library returns) |
| :--- | :--- |
| 驚蟄 | 惊蛰 |
| 穀雨 | 谷雨 |
| 小滿 | 小满 |
| 芒種 | 芒种 |
| 處暑 | 处暑 |

A lookup table keyed on the traditional forms and fed the library's output throws on
about a fifth of the year. 白露 and 秋分 are spelled identically in both, which is
why both reference charts pass regardless. Covered by
`tests/Unit/Qimen/SolarTermCoverageTest.php`.

### The lookup table

**Unverified beyond the single row exercised by chart 1.** Standard 定局歌 table:

| 節氣 | 遁 | 上元 | 中元 | 下元 |
| :--- | :---: | :---: | :---: | :---: |
| 冬至 | 陽 | 1 | 7 | 4 |
| 小寒 | 陽 | 2 | 8 | 5 |
| 大寒 | 陽 | 3 | 9 | 6 |
| 立春 | 陽 | 8 | 5 | 2 |
| 雨水 | 陽 | 9 | 6 | 3 |
| 驚蟄 | 陽 | 1 | 7 | 4 |
| 春分 | 陽 | 3 | 9 | 6 |
| 清明 | 陽 | 4 | 1 | 7 |
| 穀雨 | 陽 | 5 | 2 | 8 |
| 立夏 | 陽 | 4 | 1 | 7 |
| 小滿 | 陽 | 5 | 2 | 8 |
| 芒種 | 陽 | 6 | 3 | 9 |
| 夏至 | 陰 | 9 | 3 | 6 |
| 小暑 | 陰 | 8 | 2 | 5 |
| 大暑 | 陰 | 7 | 1 | 4 |
| 立秋 | 陰 | 2 | 5 | 8 |
| 處暑 | 陰 | 1 | 4 | 7 |
| 白露 | 陰 | 9 | 3 | 6 |
| 秋分 | 陰 | 7 | 1 | 4 |
| 寒露 | 陰 | 6 | 9 | 3 |
| 霜降 | 陰 | 5 | 8 | 2 |
| 立冬 | 陰 | 6 | 9 | 3 |
| 小雪 | 陰 | 5 | 8 | 2 |
| 大雪 | 陰 | 4 | 7 | 1 |

### The contested part: which 節氣 applies on a given day

A solar term lasts ~15.218 days; three 元 last exactly 15 days. The drift is
resolved differently by different schools.

- **拆補法** — the term switches at the exact astronomical 交節 instant. The 元
  comes from the day's 符頭. No intercalation, no 超神, no 接氣.
- **置閏法** — each term's three 元 must begin on an 上元 符頭 day (甲子/己卯/甲午/己酉)
  and run as unbroken 15-day blocks. When the 符頭 arrives before the term that is
  **超神**; when the term arrives first that is **接氣**. Once 超神 exceeds ~9 days a
  leap (置閏) repeats 芒種 or 大雪.

### Verdict for chart 1: 置閏法

**This is a correction.** The automated research reported 拆補法 with confident
sourcing. The chart contradicts it.

Working:

1. Day 乙未. Day stem 乙 → index 1, offset = 1 mod 5 = 1. Day branch 未 → index 7.
   符頭 branch = (7 − 1 + 12) mod 12 = 6 = 午 → 符頭 is **甲午** → 午 ∈ {子,卯,午,酉}
   → **上元**.
2. The 甲午 day is 2026-09-17. 秋分 arrives 2026-09-23 08:05. The 符頭 therefore
   leads the term by about 5.8 days: textbook **超神**, under the 9-day leap threshold.
3. **拆補法 predicts:** current term is still 白露 → 白露上元 → **局 9**.
4. **置閏法 predicts:** the 上元 block already started on the 甲午 day and belongs to
   the next term → 秋分上元 → **局 7**.
5. **The chart shows 局 7.**

拆補法 is refuted for this chart. 置閏法 matches.

**Chart 2 confirms it independently.** Day 丙申 → stem index 2, offset 2, branch 申
index 8 → 符頭 branch (8 − 2 + 12) mod 12 = 6 = 午 → 甲午 → 上元. Still 5 days before
秋分. 拆補法 predicts 白露上元 = 9; 置閏法 predicts 秋分上元 = 7. Chart 2 also shows 7.

> **Confidence:** two data points, both 超神, both from the same 符頭 block. That is
> the exact case separating the methods and both agree, so 拆補法 is firmly refuted
> for the reference software. Still untested: 接氣 periods, and the 芒種/大雪 leap. A third
> method that coincides on 超神 but differs on 接氣 is not yet ruled out.

**Action for the implementer:** put the method behind a config flag
(`chai_bu` | `zhi_run`), default `zhi_run`, and make the golden tests assert the
default. A flag costs almost nothing and this question is not closed.

---

## 3. 元 determination from the day's 符頭

**Verified.**

Stem indices `0:甲 1:乙 2:丙 3:丁 4:戊 5:己 6:庚 7:辛 8:壬 9:癸`
Branch indices `0:子 1:丑 2:寅 3:卯 4:辰 5:巳 6:午 7:未 8:申 9:酉 10:戌 11:亥`

```
offset     = dayStem mod 5
futouBranch = (dayBranch - offset + 12) mod 12
```

| 符頭 branch | Group | 元 |
| :--- | :--- | :--- |
| 子 卯 午 酉 (0,3,6,9) | 四仲 | 上元 |
| 寅 巳 申 亥 (2,5,8,11) | 四孟 | 中元 |
| 丑 辰 未 戌 (1,4,7,10) | 四季 | 下元 |

Chart 1: 乙未 → 符頭 甲午 → 上元. ✅

---

## 4. 地盤 (earth plate)

**Verified.** All nine palaces matched.

Stem order is fixed: `[戊, 己, 庚, 辛, 壬, 癸, 丁, 丙, 乙]`, index `k = 0..8`.
戊 always starts in palace `J` (the 局數).

```
yang: palace(k) = ((J - 1 + k) mod 9) + 1
yin : palace(k) = (((J - 1 - k) mod 9 + 9) mod 9) + 1
```

> PHP's `%` keeps the sign of the dividend, so the extra `+ 9` in the yin formula is
> load-bearing. Do not simplify it away.

Luoshu palaces: `1 坎 N · 2 坤 SW · 3 震 E · 4 巽 SE · 5 中 · 6 乾 NW · 7 兌 W · 8 艮 NE · 9 離 S`

Chart 1 (陰遁, J=7) predicted and observed:

| k | stem | palace | on chart |
| :-: | :-: | :-: | :--- |
| 0 | 戊 | 7 | W ✅ |
| 1 | 己 | 6 | NW ✅ |
| 2 | 庚 | 5 | centre (hidden) ✅ |
| 3 | 辛 | 4 | SE ✅ |
| 4 | 壬 | 3 | E ✅ |
| 5 | 癸 | 2 | SW ✅ |
| 6 | 丁 | 1 | N ✅ |
| 7 | 丙 | 9 | S ✅ |
| 8 | 乙 | 8 | NE ✅ |

---

## 5. 旬首, 值符, 值使

**Verified**, including the palace-5 branch.

```
xunBranch = (hourBranch - hourStem + 12) mod 12
```

| xunBranch | 旬 | hidden 儀 |
| :-: | :--- | :--- |
| 0 (子) | 甲子 | 戊 |
| 10 (戌) | 甲戌 | 己 |
| 8 (申) | 甲申 | 庚 |
| 6 (午) | 甲午 | 辛 |
| 4 (辰) | 甲辰 | 壬 |
| 2 (寅) | 甲寅 | 癸 |

Find that 儀 on the earth plate; its palace is `P_xun`.

Home star and gate per palace:

| Palace | 九星本位 | 八門本位 |
| :-: | :--- | :--- |
| 1 | 天蓬 | 休門 |
| 2 | 天芮 | 死門 |
| 3 | 天沖 | 傷門 |
| 4 | 天輔 | 杜門 |
| 5 | 天禽 (rides with 天芮) | none, borrows 死門 from 2 |
| 6 | 天心 | 開門 |
| 7 | 天柱 | 驚門 |
| 8 | 天任 | 生門 |
| 9 | 天英 | 景門 |

Chart 1: hour 甲申 → xunBranch = (8 − 0 + 12) mod 12 = 8 → 甲申旬 → 儀 庚 → 庚 is in
palace 5 → 值符 = 天禽 (with 天芮 in palace 2), 值使 = 死門. The chart shows 芮+禽,
符 and 死 all in SW (palace 2). ✅

---

## 6. 天盤 (heaven plate) rotation

**Verified**, both the 伏吟 branch (chart 1) and the rotating branch (chart 2).

Rule: 值符隨時干, the duty star follows the hour stem.

- Hour stem is 甲 → 甲 is hidden, so `P_target = P_xun`. Heaven plate equals earth
  plate everywhere. This is **伏吟**.
- Hour stem is not 甲 → `P_target` is the palace holding that stem on the earth plate.
- If that palace is 5 → `P_target = 2` (寄坤二宮).

Outer ring, clockwise: `[1, 8, 3, 4, 9, 2, 7, 6]`
Star cycle: `[天蓬, 天任, 天沖, 天輔, 天英, 天芮(+天禽), 天柱, 天心]`
Direction: clockwise in **both** 陽遁 and 陰遁.

Each star carries its home palace's earth stem with it. 天禽 always rides with 天芮,
so whichever palace receives 天芮 receives two heaven stems (palace 2's and palace 5's).

Chart 1 verification, all nine stars:

| Palace | predicted | on chart |
| :-: | :--- | :--- |
| 2 SW | 天芮 + 天禽 | Grain + Bird ✅ |
| 7 W | 天柱 | Pillar ✅ |
| 6 NW | 天心 | Heart ✅ |
| 1 N | 天蓬 | Grass ✅ |
| 8 NE | 天任 | Ambassador ✅ |
| 3 E | 天沖 | Destructor ✅ |
| 4 SE | 天輔 | Assistant ✅ |
| 9 S | 天英 | Hero ✅ |

Because the hour stem is 甲, every heaven stem equals its earth stem on this chart.
Confirmed in all 8 outer palaces.

### Chart 2 verification (the rotating case)

Hour 壬辰. 壬 sits in palace 3 on the earth plate, so `P_target = 3`. The duty star
天芮/天禽 moves from palace 2 to palace 3, and the rest follow clockwise.

| Palace | star predicted | on chart | heaven stem predicted | on chart |
| :-: | :--- | :--- | :-: | :--- |
| 3 E | 天芮 + 天禽 | Grain + Bird ✅ | 癸 (from 2) + 庚 (from 5) | 癸 + 庚 ✅ |
| 4 SE | 天柱 | Pillar ✅ | 戊 (from 7) | 戊 ✅ |
| 9 S | 天心 | Heart ✅ | 己 (from 6) | 己 ✅ |
| 2 SW | 天蓬 | Grass ✅ | 丁 (from 1) | 丁 ✅ |
| 7 W | 天任 | Ambassador ✅ | 乙 (from 8) | 乙 ✅ |
| 6 NW | 天沖 | Destructor ✅ | 壬 (from 3) | 壬 ✅ |
| 1 N | 天輔 | Assistant ✅ | 辛 (from 4) | 辛 ✅ |
| 8 NE | 天英 | Hero ✅ | 丙 (from 9) | 丙 ✅ |

All 9 stars and all 9 heaven stems correct, including the 天禽 double-stem rule.
The rotation path is now **verified**.

---

## 7. 八門 (gates) rotation

**Verified, after a correction.** Chart 1 passed under the original rule by accident;
chart 2 exposed it.

> ### ⚠️ Corrected rule
>
> The research spec said to convert `P_xun = 5` to palace 2 **before** stepping.
> That is wrong. You step from palace 5 itself, and divert to 2 only if the
> **landing** palace is 5.
>
> Chart 1 could not tell the difference because its step count was 0, so both
> versions landed on 5 and both diverted to 2. Chart 2 has a step count of 8, and
> the two versions diverge: the wrong rule gives palace 3, the correct rule gives
> palace 6. The chart shows palace 6.

```
P_start = P_xun          // do NOT remap 5 here
delta   = hour's position in the 旬 (0 for the 甲 hour … 9 for the 癸 hour)

yang: P_raw = ((P_start - 1 + delta) mod 9) + 1
yin : P_raw = (((P_start - 1 - delta) mod 9 + 9) mod 9) + 1

P_dest = (P_raw == 5) ? 2 : P_raw     // divert only on landing
```

Note that `delta` equals the hour stem's index only because a 旬 always begins on a
甲 hour. Deriving it from the stem index is correct but incidental; if you ever
refactor, keep the meaning ("how many double-hours since the 旬首") rather than the
shortcut.

Gate cycle: `[休門, 生門, 傷門, 杜門, 景門, 死門, 驚門, 開門]`
Place 值使 at `P_dest`, then continue the cycle clockwise around the outer ring.

Chart 1: `P_start = 2`, `delta = 0` (甲) → `P_dest = 2`, 死門 in palace 2.

| Palace | predicted | on chart |
| :-: | :--- | :--- |
| 2 SW | 死門 | Death ✅ |
| 7 W | 驚門 | Fear ✅ |
| 6 NW | 開門 | Open ✅ |
| 1 N | 休門 | Rest ✅ |
| 8 NE | 生門 | Life ✅ |
| 3 E | 傷門 | Harm ✅ |
| 4 SE | 杜門 | Delusion ✅ |
| 9 S | 景門 | Scenery ✅ |

Chart 2: hour 壬辰 is the 9th hour of 甲申旬, so `delta = 8`. 陰遁, stepping back 8
from palace 5: `5→4→3→2→1→9→8→7→6`. `P_dest = 6`, no diversion needed.

| Palace | predicted | on chart |
| :-: | :--- | :--- |
| 6 NW | 死門 | Death ✅ |
| 1 N | 驚門 | Fear ✅ |
| 8 NE | 開門 | Open ✅ |
| 3 E | 休門 | Rest ✅ |
| 4 SE | 生門 | Life ✅ |
| 9 S | 傷門 | Harm ✅ |
| 2 SW | 杜門 | Delusion ✅ |
| 7 W | 景門 | Scenery ✅ |

---

## 8. 八神 (deities) rotation

**Verified**, including the yin counter-clockwise direction.

值符 lands in the palace where the duty star landed (`P_target`).

Order: `[值符, 螣蛇, 太陰, 六合, 白虎, 玄武, 九地, 九天]`

Direction, and unlike the stars and gates this one does flip:

- 陽遁: clockwise, `1 → 8 → 3 → 4 → 9 → 2 → 7 → 6`
- 陰遁: counter-clockwise, `1 → 6 → 7 → 2 → 9 → 4 → 3 → 8`

Chart 1 is 陰遁, 值符 at palace 2:

| Palace | predicted | on chart |
| :-: | :--- | :--- |
| 2 SW | 值符 | Chief ✅ |
| 9 S | 螣蛇 | Snake ✅ |
| 4 SE | 太陰 | Yin Moon ✅ |
| 3 E | 六合 | Harmony ✅ |
| 8 NE | 白虎 | Tiger ✅ |
| 1 N | 玄武 | Tortoise ✅ |
| 6 NW | 九地 | Earth ✅ |
| 7 W | 九天 | Heaven ✅ |

Chart 2 is also 陰遁, with 值符 at palace 3. Counter-clockwise from 3:

| Palace | predicted | on chart |
| :-: | :--- | :--- |
| 3 E | 值符 | Chief ✅ |
| 8 NE | 螣蛇 | Snake ✅ |
| 1 N | 太陰 | Yin Moon ✅ |
| 6 NW | 六合 | Harmony ✅ |
| 7 W | 白虎 | Tiger ✅ |
| 2 SW | 玄武 | Tortoise ✅ |
| 9 S | 九地 | Earth ✅ |
| 4 SE | 九天 | Heaven ✅ |

Note on naming: some lineages swap positions 5 and 6 to 勾陳 and 朱雀 during 陰遁.
Both charts are 陰遁 and both print **Tiger** and **Tortoise**, so the reference software does
**not** swap. Use the fixed names. ✅

---

## 9. Palace 5 (寄坤二宮)

**Verified** for the earth-plate and 值符/值使 cases.

1. Palace 5 holds an earth stem, which is also treated as resident in palace 2.
2. 天禽 never appears in palace 5 on the heaven plate; it always rides with 天芮 and
   carries palace 5's earth stem along.
3. Palace 5 has no gate. A gate whose step lands on 5 is diverted to 2.
4. Deities never occupy palace 5.
5. The reference software uses 中五永寄坤二 (always Kun 2), **not** the 陽寄坤二／陰寄艮八 variant.
   Chart 1 is 陰遁 and sends palace 5 to Kun 2, which confirms this. ✅

---

## 10. Time basis

**Verified: clock time, not longitude-corrected true solar time.**

Chart 2 is stamped 07:08 and shows hour pillar **壬辰**. 辰時 runs 07:00–09:00 by
clock, so 07:08 is 辰時 and the pillar is 壬辰.

Malaysia sits at roughly 101.7°E while its timezone is UTC+8 (120°E), so true local
solar time runs about 53 minutes behind the clock. Under a solar-time correction
07:08 would become ~06:15, which is 卯時, giving hour pillar **辛卯** — confirmed by
running `6tail/lunar-php` on 06:15.

The chart shows 壬辰. Therefore no longitude correction is applied. Feed the engine
plain local clock time.

> One caveat: 07:08 is only 8 minutes into 辰時, which makes this a genuinely
> discriminating sample. But a chart taken within a couple of minutes of an odd hour
> would nail the boundary behaviour exactly (does 09:00:00 belong to 辰 or 巳?).

---

## Terminology (reference software English naming)

### 九星

| 中文 | Reference software | Element | Home |
| :--- | :--- | :--- | :-: |
| 天蓬 | Grass | Water | 1 |
| 天芮 | Grain | Earth | 2 |
| 天沖 | Destructor | Wood | 3 |
| 天輔 | Assistant | Wood | 4 |
| 天禽 | Bird | Earth | 5 |
| 天心 | Heart | Metal | 6 |
| 天柱 | Pillar | Metal | 7 |
| 天任 | Ambassador | Earth | 8 |
| 天英 | Hero | Fire | 9 |

### 八門

| 中文 | Reference software | Element | Home |
| :--- | :--- | :--- | :-: |
| 休門 | Rest | Water | 1 |
| 死門 | Death | Earth | 2 |
| 傷門 | Harm | Wood | 3 |
| 杜門 | Delusion | Wood | 4 |
| 開門 | Open | Metal | 6 |
| 驚門 | Fear | Metal | 7 |
| 生門 | Life | Earth | 8 |
| 景門 | Scenery | Fire | 9 |

### 八神

| 中文 | Reference software |
| :--- | :--- |
| 值符 | Chief |
| 螣蛇 | Snake |
| 太陰 | Yin Moon |
| 六合 | Harmony |
| 白虎 | Tiger |
| 玄武 | Tortoise |
| 九地 | Earth |
| 九天 | Heaven |

All 25 names above are taken from chart 1's own labels, so they are verified.

---

## Cross-check against an independent implementation

`kinqimen` 0.0.6.6 ([repo](https://github.com/kentang2017/kinqimen)) was
installed and run against both reference datetimes. It exposes both 局數 methods via
`pan(option)`: `1` = 拆補法, `2` = 置閏法.

### 局數

| Chart | 拆補法 | 置閏法 | Reference chart |
| :--- | :---: | :---: | :---: |
| A (2026-09-18 16:14) | 9 | **7** | **7** |
| B (2026-09-19 07:08) | 9 | **7** | **7** |

置閏法 confirmed by a third independent source. 拆補法 refuted again.

### Full plate comparison

Every palace of both charts under `pan(2)` was compared against the screenshots:

| Layer | Chart A | Chart B |
| :--- | :---: | :---: |
| Earth stems | 9/9 | 9/9 |
| Heaven stems | 8/8 | 8/8 |
| Stars | 8/8 | 8/8 |
| Gates | 8/8 | 8/8 |
| Deities | 8/8 | 8/8 |

**66 of 66.** No discrepancies.

Chart B's gate row matters most: `kinqimen` places 死門 in palace 6 (乾), which is
what the **corrected** rule in [Section 7](#7-八門-gates-rotation) predicts. The
original rule predicted palace 3. The correction is now confirmed independently.

### Library quirks (not defects in this spec)

- It substitutes 芮 with 禽 and never returns 天芮. The reference software prints both in the same
  palace. Cosmetic, but a direct string comparison against library output will fail.
- It omits palace 5 from the heaven plate on rotated charts.
- Its root `旬首` key is the **day** pillar's 旬首, not the hour's. The hour's is at
  `值符值使.值符天干[0]`. Easy to misread.

### Its 置閏 behaviour matches the classical rule

`kinqimen/config.py` contains a branch guarded by `dgz_dist != "日干是符頭"`, which
reads as though the library refuses to advance the solar term on a 符頭 day. That
reading is **wrong** — the function has further branches that handle the case.

Behaviour was measured rather than inferred, by running the library directly:

| Date | Day | 拆補法 局 | 置閏法 局 |
| :--- | :--- | :---: | :---: |
| 2026-09-20 | 丁酉 | 9 | 7 |
| 2026-09-22 | 己亥 (符頭) | 3 | **1** |
| 2026-09-24 | 辛丑 | 1 | 1 |

On 2026-09-22, a 符頭 day, 置閏法 still advances to 秋分 and returns 局 1. That is
exactly what the classical rule in Section 2 predicts. There is no 符頭-day
exception, and this spec and `kinqimen` agree.

From 2026-09-24 the two methods converge, because 秋分 arrives 2026-09-23 08:05 and
the calendar catches up.

> **Lesson recorded:** the divergence was originally asserted from reading the
> library's source. Running it disproved that. Read source to form a hypothesis;
> run it to settle one.

Open-source implementations, useful for generating extra fixtures. **None of these
have been inspected or run yet** — treat the descriptions as unverified claims from
the research pass, not as facts.

| Repo | Language | Claimed method support |
| :--- | :--- | :--- |
| [atopx/qimen](https://github.com/atopx/qimen) | Go | 拆補 and 置閏, switchable |
| [kentang2017/kinqimen](https://github.com/kentang2017/kinqimen) | Python | 拆補 and 置閏 |
| [perfhelf/bigfishmarquis-qimen](https://github.com/perfhelf/bigfishmarquis-qimen) | TypeScript | 拆補, 茅山, 置閏 |
| [oceanjustinlin/qimen](https://github.com/oceanjustinlin/qimen) | JS/TS | 拆補 only |
| [deminzhang/qimen-go](https://github.com/deminzhang/qimen-go) | Go | 拆補 only |

The ones supporting 置閏 are the relevant ones given Section 2.

---

## Coverage so far

| Rule | Chart 1 | Chart 2 | Status |
| :--- | :---: | :---: | :--- |
| 陰遁 detection | ✅ | ✅ | verified |
| 陽遁 detection | — | — | **untested** |
| 局數 via 置閏法 | ✅ | ✅ | verified for 超神 only |
| 元 from 符頭 | ✅ | ✅ | verified |
| Earth plate (陰遁) | ✅ | ✅ | verified |
| Earth plate (陽遁) | — | — | **untested** |
| 旬首 → 值符/值使 | ✅ | ✅ | verified, incl. palace 5 |
| Heaven plate 伏吟 | ✅ | — | verified |
| Heaven plate rotating | — | ✅ | verified |
| Gates (delta = 0) | ✅ | — | verified |
| Gates (delta > 0) | — | ✅ | verified, **rule corrected** |
| Deities 陰遁 CCW | ✅ | ✅ | verified |
| Deities 陽遁 CW | — | — | **untested** |
| Palace 5 diversion | ✅ | ✅ | verified |
| Clock vs solar time | — | ✅ | verified |
| English names | ✅ | ✅ | verified |

## Project decision: kinqimen is the oracle

**Decided 2026-09-19 by the project owner.** `kinqimen` 0.0.6.6 under `pan(2)` 置閏法
is accepted as the definition of a correct chart. No further reference chart screenshots are
required before the engine is considered verified.

### What this buys

The remaining gaps were all blocked on waiting for real charts. 陽遁 needed a
screenshot taken after the winter solstice; 接氣 needed one from the right week of the
year. None of that is needed now. Fixtures for any date, any 遁, any solar term can be
generated on demand and turned into golden tests today.

### What this costs

`kinqimen` is now the definition of correct, not the reference software. If the two ever disagree,
this engine follows `kinqimen` and is by definition wrong about the reference charts.

The risk is bounded by what was actually measured: across the two real screenshots,
all 66 fields agree. That is two charts, both 陰遁, both in the same 超神 stretch. The
agreement has never been tested in 陽遁, during 接氣, or across a 芒種/大雪 leap.

If a reference chart ever turns up that contradicts the engine, the fault is most
likely in one of those three untested regions, and the fix is to correct the engine
rather than to re-open the method question.

### Consequences for the test suite

- The pending test waiting on a 2026-09-22 screenshot is obsolete.
- Extra golden fixtures should be generated from `kinqimen`, not waited for. Priority
  order: 陽遁 (three rules reverse direction there and none have run that way), then
  接氣, then a 芒種/大雪 leap, then a spread across all 24 solar terms.
- Fixtures generated from `kinqimen` must be labelled as such, so that a fixture
  derived from a screenshot is never confused with one derived from the oracle.

## What remains unverified against a real chart

Recorded for honesty, not as blockers.

1. **陽遁** — every verified chart is 陰遁.
2. **接氣 periods and the 芒種/大雪 leap** — every verified date sits in one 超神 stretch.
3. **69 of 72 局 table cells.**
4. **時辰 boundary behaviour** — clock time is settled, but which side of 09:00:00
   belongs to which 時辰 is not.
