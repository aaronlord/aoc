<?php

declare(strict_types=1);

namespace App\Aoc\Day1;

use App\Aoc\SolutionInterface;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\File;

class Solution implements SolutionInterface
{
    public function run(string $inputPath): int
    {
        $left = $right = [];

        foreach (File::lines($inputPath)->filter() as $line) {
            [$left[], $right[]] = explode('   ', $line);
        }

        return collect($right)
            ->sort()
            ->chunkWhile(static fn (string $value, int $key, Collection $chunk): bool => $value === $chunk->last())
            ->mapWithKeys(static fn (Collection $chunk): array => [$chunk->first() => $chunk->count()])
            ->pipe(static fn (Collection $counts): int => collect($left)
                ->reduce(static fn (int $carry, string $value): int => $carry + $value * $counts->get($value), 0)
            );
    }
}
