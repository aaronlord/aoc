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
            ->reduce($this->sumCalibrationValues(...), 0);

        $this->line($sum); // 54845
    }

    private function sumCalibrationValues(int $carry, string $line): int
    {
        return $carry + $this->calibrationValue($line);
    }

    private function calibrationValue(string $line): int
    {
        $tens = null;
        $ones = null;

        for ($i = 0; $i < strlen($line); $i++) {
            if (! $tens && $digit = $this->numberize(substr($line, $i))) {
                $tens = $digit;
            }

            if (! $ones && $digit = $this->numberize(substr($line, abs($i + 1) * -1))) {
                $ones = $digit;
            }

            if ($tens && $ones) {
                break;
            }
        }

        return ($tens * 10) + $ones;
    }

    private function numberize(string $substr): ?int
    {
        if (is_numeric($substr[0])) {
            return (int) $substr[0];
        }

        return match (true) {
            str_starts_with($substr, 'one') => 1,
            str_starts_with($substr, 'two') => 2,
            str_starts_with($substr, 'three') => 3,
            str_starts_with($substr, 'four') => 4,
            str_starts_with($substr, 'five') => 5,
            str_starts_with($substr, 'six') => 6,
            str_starts_with($substr, 'seven') => 7,
            str_starts_with($substr, 'eight') => 8,
            str_starts_with($substr, 'nine') => 9,
            default => null,
        };
    }
}
