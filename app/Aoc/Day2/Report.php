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
        for ($i = 1; $i < count($this->levels); $i++) {
            $a = (int) $this->levels[$i - 1];
            $b = (int) $this->levels[$i];

            if (! $this->isCorrectlyOrdered($a, $b)) {
                return false;
            }

            $diff = abs($a - $b);

            if ($diff < 1 || $diff > 3) {
                return false;
            }
        }

        return true;
    }

    protected function isCorrectlyOrdered(int $a, int $b): bool
    {
        if ($this->shouldBeAscending()) {
            return $a < $b;
        }

        return $a > $b;
    }

    protected function shouldBeAscending(): bool
    {
        return $this->levels[0] < $this->levels[1];
    }
}
