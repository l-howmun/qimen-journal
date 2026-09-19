<?php

declare(strict_types=1);

namespace App\Qimen\Enums;

use ValueError;

enum SolarTerm: string
{
    case DongZhi = '冬至';
    case XiaoHan = '小寒';
    case DaHan = '大寒';
    case LiChun = '立春';
    case YuShui = '雨水';
    case JingZhe = '惊蛰';
    case ChunFen = '春分';
    case QingMing = '清明';
    case GuYu = '谷雨';
    case LiXia = '立夏';
    case XiaoMan = '小满';
    case MangZhong = '芒种';
    case XiaZhi = '夏至';
    case XiaoShu = '小暑';
    case DaShu = '大暑';
    case LiQiu = '立秋';
    case ChuShu = '处暑';
    case BaiLu = '白露';
    case QiuFen = '秋分';
    case HanLu = '寒露';
    case ShuangJiang = '霜降';
    case LiDong = '立冬';
    case XiaoXue = '小雪';
    case DaXue = '大雪';

    /**
     * Map from traditional Chinese characters to canonical SolarTerm cases.
     *
     * @var array<string, self>
     */
    private const array TRADITIONAL_MAP = [
        '驚蟄' => self::JingZhe,
        '穀雨' => self::GuYu,
        '小滿' => self::XiaoMan,
        '芒種' => self::MangZhong,
        '處暑' => self::ChuShu,
    ];

    /**
     * Normalise a solar term name from simplified or traditional Chinese.
     */
    public static function fromName(string|self $name): self
    {
        if ($name instanceof self) {
            return $name;
        }

        return self::tryFrom($name)
            ?? self::TRADITIONAL_MAP[$name] ?? null
            ?? throw new ValueError("\"{$name}\" is not a valid solar term.");
    }

    /**
     * Return the traditional Chinese name for this solar term.
     */
    public function traditionalName(): string
    {
        return match ($this) {
            self::JingZhe => '驚蟄',
            self::GuYu => '穀雨',
            self::XiaoMan => '小滿',
            self::MangZhong => '芒種',
            self::ChuShu => '處暑',
            default => $this->value,
        };
    }

    /**
     * Standard 定局歌 lookup: [Dun, 上元 局數, 中元 局數, 下元 局數]
     *
     * @return array{Dun, int, int, int}
     */
    public function juInfo(): array
    {
        return match ($this) {
            self::DongZhi => [Dun::Yang, 1, 7, 4],
            self::XiaoHan => [Dun::Yang, 2, 8, 5],
            self::DaHan => [Dun::Yang, 3, 9, 6],
            self::LiChun => [Dun::Yang, 8, 5, 2],
            self::YuShui => [Dun::Yang, 9, 6, 3],
            self::JingZhe => [Dun::Yang, 1, 7, 4],
            self::ChunFen => [Dun::Yang, 3, 9, 6],
            self::QingMing => [Dun::Yang, 4, 1, 7],
            self::GuYu => [Dun::Yang, 5, 2, 8],
            self::LiXia => [Dun::Yang, 4, 1, 7],
            self::XiaoMan => [Dun::Yang, 5, 2, 8],
            self::MangZhong => [Dun::Yang, 6, 3, 9],
            self::XiaZhi => [Dun::Yin, 9, 3, 6],
            self::XiaoShu => [Dun::Yin, 8, 2, 5],
            self::DaShu => [Dun::Yin, 7, 1, 4],
            self::LiQiu => [Dun::Yin, 2, 5, 8],
            self::ChuShu => [Dun::Yin, 1, 4, 7],
            self::BaiLu => [Dun::Yin, 9, 3, 6],
            self::QiuFen => [Dun::Yin, 7, 1, 4],
            self::HanLu => [Dun::Yin, 6, 9, 3],
            self::ShuangJiang => [Dun::Yin, 5, 8, 2],
            self::LiDong => [Dun::Yin, 6, 9, 3],
            self::XiaoXue => [Dun::Yin, 5, 8, 2],
            self::DaXue => [Dun::Yin, 4, 7, 1],
        };
    }

    public function dun(): Dun
    {
        return $this->juInfo()[0];
    }

    public function ju(Yuan $yuan): int
    {
        return match ($yuan) {
            Yuan::Shang => $this->juInfo()[1],
            Yuan::Zhong => $this->juInfo()[2],
            Yuan::Xia => $this->juInfo()[3],
        };
    }
}
