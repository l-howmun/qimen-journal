<?php

declare(strict_types=1);

namespace App\Qimen\Enums;

enum Gate: string
{
    case Xiu = '休門';
    case Sheng = '生門';
    case Shang = '傷門';
    case Du = '杜門';
    case Jing = '景門';
    case Si = '死門';
    case JingShock = '驚門';
    case Kai = '開門';
}
