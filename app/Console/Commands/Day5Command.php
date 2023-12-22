<?php

namespace App\Console\Commands;

use App\Console\Commands\Day5\Map;
use App\Console\Commands\Day5\Seeds;
use Illuminate\Console\Command;
use Illuminate\Contracts\Filesystem\Filesystem;
use Illuminate\Support\Facades\Pipeline;
use Illuminate\Support\Str;

class Day5Command extends Command
{
    protected $signature = 'day:5';

    protected $description = 'https://adventofcode.com/2023/day/5';

    public function handle(Filesystem $fs): void
    {
        $contents = $fs->get('day5.txt');
        $chunks = explode("\n\n", $contents);

        $seeds = Seeds::fromString(array_shift($chunks));

        $maps = collect($chunks)
            ->reject(fn (string $line): bool => empty($line))
            ->map(fn (string $chunk): Map => Map::fromString($chunk));

        $seeds = Pipeline::send($seeds)
            ->through($maps->toArray())
            ->then(fn (Seeds $seeds) => $seeds);

        $this->line($seeds->min());
    }
}

namespace App\Console\Commands\Day5;

use Illuminate\Support\Str;
use Closure;

class Seeds
{
    /**
     * @param array<int> $items
     */
    private function __construct(
        public readonly array $items = []
    ) {
    }

    public static function fromString(string $string): self
    {
        return new self(array_map(
            fn (string $string) => Seed::fromString($string),
            array_filter(explode(' ', Str::after($string, ':')))
        ));
    }

    public function min(): int
    {
        return min(array_column($this->items, 'value'));
    }
}

class Seed
{
    private function __construct(
        public readonly int $number,
        public int $value
    ) {
    }

    public static function fromString(string $string): self
    {
        return new self((int) $string, (int) $string);
    }
}

class Map
{
    /**
     * @param array<Range> $ranges
     */
    private function __construct(
        public readonly array $ranges = []
    ) {
    }

    public static function fromString(string $string): self
    {
        $lines = array_slice(array_filter(explode("\n", $string)), 1);

        return new self(array_map(fn (string $line) => Range::fromString($line), $lines));
    }

    /**
     * @param Closure(Seeds): Seeds $next
     */
    public function handle(Seeds $seeds, Closure $next): Seeds
    {
        foreach ($seeds->items as $seed) {
            $seed->value = $this->next($seed->value);
        }

        return $next($seeds);
    }

    private function next(int $number): int
    {
        foreach ($this->ranges as $range) {
            if ($number >= $range->sourceStart && $number <= $range->sourceEnd) {
                return $range->destinationStart + ($number - $range->sourceStart);
            }
        }

        return $number;
    }
}

class Range
{
    private function __construct(
        public readonly int $sourceStart,
        public readonly int $sourceEnd,
        public readonly int $destinationStart,
        public readonly int $destinationEnd
    ) {
    }

    public static function fromString(string $line): self
    {
        [$destination, $source, $length] = explode(' ', $line);

        return new self(
            sourceStart: $source,
            sourceEnd: $source + $length - 1,
            destinationStart: $destination,
            destinationEnd: $destination + $length - 1,
        );
    }
}
