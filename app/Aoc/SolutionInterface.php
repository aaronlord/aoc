<?php

declare(strict_types=1);

namespace App\Aoc;

interface SolutionInterface
{
    public function run(string $inputPath): int;
}
