<?php

declare(strict_types=1);

namespace App\Aoc\Day2;

class Report
{
    /**
     * @param array<int> $levels
     */
    public function __construct(
        protected array $levels
    ) {
    }

    public function isSafe(): bool
    {
        if ($this->checkLevels($this->levels)) {
            return true;
        }

        for ($i = 0; $i < count($this->levels); $i++) {
            $levels = $this->levels;

            unset($levels[$i]);

            if ($this->checkLevels(array_values($levels))) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param array<int> $levels
     */
    protected function checkLevels(array $levels): bool
    {
        for ($i = 1; $i < count($levels); $i++) {
            $a = (int) $levels[$i - 1];
            $b = (int) $levels[$i];

            if (! $this->isCorrectlyOrdered($a, $b, $this->shouldBeAscending($levels))) {
                return false;
            }

            $diff = abs($a - $b);

            if ($diff < 1 || $diff > 3) {
                return false;
            }
        }

        return true;
    }

    protected function isCorrectlyOrdered(int $a, int $b, bool $shouldBeAscending): bool
    {
        if ($shouldBeAscending) {
            return $a < $b;
        }

        return $a > $b;
    }

    /**
     * @param array<int> $levels
     */
    protected function shouldBeAscending(array $levels): bool
    {
        return $levels[0] < $levels[1];
    }
}
