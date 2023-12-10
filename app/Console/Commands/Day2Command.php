<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Contracts\Filesystem\Filesystem;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class Day2Command extends Command
{
    protected $signature = 'day:2';

    protected $description = 'https://adventofcode.com/2023/day/2';

    public function handle(Filesystem $fs): void
    {
        $contents = $fs->get('day2.txt');
        $lines = explode("\n", $contents);

        $sum = collect($lines)
            ->reject(fn (string $line): bool => empty($line))
            ->map(fn (string $line): Game => Game::fromLine($line))
            ->reduce($this->reduce(...), 0);

        $this->line($sum);
    }

    private function reduce(int $carry, Game $game): int
    {
        return $carry + $game->powerOfMinimumSet();
    }
}

class Game
{
    private function __construct(
        public readonly int $id,
        public readonly Handfulls $handfulls,
    ) {
    }

    public static function fromLine(string $line): self
    {
        return new self(
            id: (int) trim(Str::afterLast(Str::before($line, ':'), ' ')),
            handfulls: Handfulls::fromLine(trim(Str::after($line, ': '))),
        );
    }

    public function powerOfMinimumSet(): int
    {
        $set = $this->handfulls->minimumSet();

        return $set->red * $set->green * $set->blue;
    }
}

class Handfulls extends Collection
{
    public static function fromLine(string $line): self
    {
        return new self(array_map(
            fn (string $ln): Handfull => Handfull::fromLine($ln),
            explode('; ', $line)
        ));
    }

    public function minimumSet(): Set
    {
        return new Set(
            red: $this->max(fn (Handfull $handfull): int => $handfull->red),
            green: $this->max(fn (Handfull $handfull): int => $handfull->green),
            blue: $this->max(fn (Handfull $handfull): int => $handfull->blue),
        );
    }
}

class Set
{
    public function __construct(
        public readonly int $red,
        public readonly int $green,
        public readonly int $blue,
    ) {
    }
}

class Handfull extends Set
{
    public static function fromLine(string $line): self
    {
        $dice = collect(explode(', ', $line))
            ->map(fn (string $ln): array => explode(' ', $ln))
            ->mapWithKeys(fn (array $arr): array => [$arr[1] => (int) $arr[0]]);

        return new self(
            red: $dice->get('red', 0),
            green: $dice->get('green', 0),
            blue: $dice->get('blue', 0),
        );
    }
}
