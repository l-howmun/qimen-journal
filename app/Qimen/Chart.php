<?php

declare(strict_types=1);

namespace App\Qimen;

use App\Qimen\Enums\Dun;
use App\Qimen\Enums\Gate;
use App\Qimen\Enums\Star;
use App\Qimen\Enums\Yuan;
use InvalidArgumentException;

readonly class Chart
{
    /**
     * @param  array<int, Palace>  $palaces
     */
    public function __construct(
        public string $dayPillar,
        public string $hourPillar,
        public string $solarTerm,
        public string $juSolarTerm,
        public Dun $dun,
        public int $ju,
        public Yuan $yuan,
        public string $xunShou,
        public Star $dutyStar,
        public Gate $dutyGate,
        public bool $isFuYin,
        public array $palaces,
    ) {}

    public function palace(int $number): Palace
    {
        if ($number < 1 || $number > 9) {
            throw new InvalidArgumentException("Palace number must be between 1 and 9, {$number} given.");
        }

        return $this->palaces[$number];
    }
}
