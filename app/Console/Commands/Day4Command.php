<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Contracts\Filesystem\Filesystem;
use App\Console\Commands\Day4\Card;

class Day4Command extends Command
{
    protected $signature = 'day:4';

    protected $description = 'https://adventofcode.com/2023/day/4';

    public function handle(Filesystem $fs): void
    {
        $contents = $fs->get('day4.txt');
        $lines = explode("\n", $contents);

        $sum = collect($lines)
            ->reject(fn (string $line): bool => empty($line))
            ->map(fn (string $line): Card => Card::fromString($line))
            ->reduce(fn (int $carry, Card $card): int => $carry + $card->calculatePoints(), 0);

        $this->line($sum);
    }
}

namespace App\Console\Commands\Day4;

use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class Card
{
    public function __construct(
        private int $id,
        private Numbers $mine,
        private Numbers $winning,
    ) {
    }

    public static function fromString(string $line): self
    {
        $numbers = Str::after($line, ':');

        return new self(
            Str::afterLast(Str::before($line, ':'), ' '),
            Numbers::fromString(Str::before($numbers, '|')),
            Numbers::fromString(Str::after($numbers, '|')),
        );
    }

    public function calculatePoints(): int
    {
        $n = $this->mine
            ->intersect($this->winning)
            ->count() - 1;

        // 1 x 2n
        return 1 * (2 ** $n);
    }
}

class Numbers extends Collection
{
    public static function fromString(string $numbers): self
    {
        return new self(array_filter(explode(' ', $numbers)));
    }
}
