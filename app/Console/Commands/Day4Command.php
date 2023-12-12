<?php

namespace App\Console\Commands;

use App\Console\Commands\Day4\Card;
use Illuminate\Console\Command;
use Illuminate\Contracts\Filesystem\Filesystem;
use Illuminate\Support\Collection;

class Day4Command extends Command
{
    protected $signature = 'day:4';

    protected $description = 'https://adventofcode.com/2023/day/4';

    public function handle(Filesystem $fs): void
    {
        $contents = $fs->get('day4.txt');
        $lines = explode("\n", $contents);

        $cards = collect($lines)
            ->reject(fn (string $line): bool => empty($line))
            ->map(fn (string $line): Card => Card::fromString($line));

        $sum = $cards->count() + $cards->reduce(fn (int $carry, Card $card, int $i): int => $this->rec($carry, $card, $i, $cards), 0);

        $this->line($sum);
    }

    public function rec(int $carry, Card $card, int $i, Collection $cards): int
    {
        $copies = $cards->slice($i + 1, $card->matchingNumbers());

        return $carry
            + $copies->count()
            + $copies->reduce(fn (int $carry, Card $card, $i): int => $this->rec($carry, $card, $i, $cards), 0);
    }
}

namespace App\Console\Commands\Day4;

use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class Card
{
    public function __construct(
        public int $id,
        public Numbers $mine,
        public Numbers $winning,
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

    public function matchingNumbers(): int
    {
        return $this->mine
            ->intersect($this->winning)
            ->count();
    }
}

class Numbers extends Collection
{
    public static function fromString(string $numbers): self
    {
        return new self(array_filter(explode(' ', $numbers)));
    }
}
