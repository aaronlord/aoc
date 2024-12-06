<?php

declare(strict_types=1);

namespace App\Aoc\Day2;

use App\Aoc\SolutionInterface;
use Illuminate\Support\Facades\File;

class Solution implements SolutionInterface
{
    public function run(string $inputPath): int
    {
        return File::lines($inputPath)
            ->filter()
            ->map(static fn (string $line) => new Report(explode(' ', $line)))
            ->reduce(static fn (int $carry, Report $report) => $carry + ($report->isSafe() ? 1 : 0), 0);
    }
}
