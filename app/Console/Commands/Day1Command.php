<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Contracts\Filesystem\Filesystem;

class Day1Command extends Command
{
    protected $signature = 'day:1';

    protected $description = 'https://adventofcode.com/2023/day/1';

    public function handle(Filesystem $fs): void
    {
        $contents = $fs->get('day1.txt');
        $lines = explode("\n", $contents);

        $sum = collect($lines)
            ->reject(fn (string $line): bool => empty($line))
            ->reduce($this->reduce(...), 0);

        $this->line($sum);
    }

    private function reduce(int $carry, string $line): int
    {
        $line = str_split($line);

        return $carry + $this->calibrationValue($line);
    }

    /**
     * @param array<string> $line
     */
    private function calibrationValue(array $line): int
    {
        return ($this->firstDigit($line) * 10) + $this->lastDigit($line);
    }

    /**
     * @param array<string> $line
     */
    private function firstDigit(array $line): int
    {
        return collect($line)
            ->first(fn ($s) => is_numeric($s));
    }

    /**
     * @param array<string> $line
     */
    private function lastDigit(array $line): int
    {
        return $this->firstDigit(array_reverse($line));
    }
}
