<?php

namespace App\Console\Commands;

use App\Console\Commands\Day9\History;
use Illuminate\Console\Command;
use Illuminate\Contracts\Filesystem\Filesystem;

class Day9Command extends Command
{
    protected $signature = 'day:9 {--test}';

    protected $description = 'https://adventofcode.com/2023/day/9';

    public function handle(Filesystem $fs): void
    {
        $contents = $fs->get($this->option('test') ? 'day9.test.txt' : 'day9.txt');
        $contents = explode("\n", $contents);

        $answer = collect($contents)
            ->filter()
            ->map(fn (string $line) => History::from($line)->value())
            ->sum();

        $this->line($answer);
    }
}

namespace App\Console\Commands\Day9;

class History
{
    /**
     * @param  array<int>  $numbers
     */
    private function __construct(
        private array $numbers,
    ) {
    }

    public static function from(string $line): static
    {
        $numbers = explode(' ', $line);

        $numbers = array_map(fn (string $number) => (int) $number, $numbers);

        return new static($numbers);
    }

    public function value(): int
    {
        $sequence = $this->sequence($this->numbers);

        return $this->extrapolate($sequence);
    }

    /**
     * @param  array<array<int>>  $sequence
     */
    private function extrapolate(array $sequence): int
    {
        $value = 0;

        for ($i = count($sequence) - 1; $i >= 0; $i--) {
            $value += end($sequence[$i]);
        }

        return $value;
    }

    /**
     * @param  array<int>  $numbers
     * @return array<array<int>>
     */
    private function sequence(array $numbers): array
    {
        $sequence = [$numbers];

        do {
            $sequence[] = $numbers = $this->steps($numbers);
        } while (array_unique($numbers) !== [0]);

        return $sequence;
    }

    /**
     * @param  array<int>  $numbers
     * @return array<int>
     */
    private function steps(array $numbers): array
    {
        $steps = [];

        for ($i = 0; $i < count($numbers) - 1; $i++) {
            $l = $numbers[$i];
            $r = $numbers[$i + 1];

            $steps[] = $r - $l;
        }

        return $steps;
    }
}
