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
            ->filter(fn (Game $game): bool => $game->isPossible())
            ->reduce($this->reduce(...), 0);

        $this->line($sum);
    }

    private function reduce(int $carry, Game $game): int
    {
        return $carry + $game->id;
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

    public function isPossible(): bool
    {
        return $this->handfulls->every(fn (Handfull $handfull): bool => $handfull->isPossible());
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
}

class Handfull
{
    public function __construct(
        public readonly int $red,
        public readonly int $green,
        public readonly int $blue,
    ) {
    }

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

    public function isPossible(): bool
    {
        if ($this->red > 12) {
            return false;
        }

        if ($this->green > 13) {
            return false;
        }

        if ($this->blue > 14) {
            return false;
        }

        return true;
    }
}
