<?php

declare(strict_types=1);

namespace App\Aoc\Day1;

use App\Aoc\SolutionInterface;
use Illuminate\Support\Facades\File;

class Solution implements SolutionInterface
{
    public function run(string $inputPath): int
    {
        $left = $right = [];

        foreach (File::lines($inputPath)->filter() as $line) {
            [$left[], $right[]] = explode('   ', $line);
        }

        sort($left);
        sort($right);

        $sum = 0;

        for ($i = 0; $i < count($left); $i++) {
            $sum += abs($left[$i] - $right[$i]);
        }

        return $sum;
    }
}
