<?php

declare(strict_types=1);

namespace App\Qimen;

use App\Qimen\Enums\Deity;
use App\Qimen\Enums\Gate;
use App\Qimen\Enums\Star;
use App\Qimen\Enums\Stem;

readonly class Palace
{
    /**
     * @param  array<int, Stem>  $heavenStems
     * @param  array<int, Star>  $stars
     */
    public function __construct(
        public int $number,
        public Stem $earthStem,
        public array $heavenStems,
        public array $stars,
        public ?Gate $gate = null,
        public ?Deity $deity = null,
    ) {}
}
