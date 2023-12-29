<?php

namespace App\Console\Commands;

use App\Console\Commands\Day5\Map;
use App\Console\Commands\Day5\Range;
use App\Console\Commands\Day5\Seeds;
use Illuminate\Console\Command;
use Illuminate\Contracts\Filesystem\Filesystem;
use Illuminate\Support\Collection;

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

        $this->line($this->min($seeds, $maps));
    }

    /**
     * @param  Collection<Map>  $maps
     */
    public function min(Map $current, Collection $maps): int
    {
        if ($maps->isEmpty()) {
            return min(array_column($current->ranges, 'sourceStart'));
        }

        $next = $maps->shift();

        $toRec = [];

        foreach ($current->ranges as $i => $currentRange) {
            foreach ($next->ranges as $nextRange) {
                if ($currentRange->sourceStart > $nextRange->sourceEnd) {
                    continue;
                }

                $newRange = new Range(
                    sourceStart: $nextRange->next(
                        $currentRange->sourceStart > $nextRange->sourceStart
                            ? $currentRange->sourceStart
                            : $nextRange->sourceStart
                    ),
                    sourceEnd: $nextRange->next(
                        $currentRange->sourceEnd > $nextRange->sourceEnd
                            ? $nextRange->sourceEnd
                            : $currentRange->sourceEnd
                    ),
                );

                $toRec[$i][] = $newRange;

                if ($currentRange->sourceEnd < $nextRange->sourceEnd) {
                    break;
                }
            }

            if (empty($toRec[$i])) {
                $toRec[$i][] = clone $currentRange;
            }
        }

        $toRec = new Map(array_merge(...$toRec));

        return $this->min($toRec, $maps);
    }
}

namespace App\Console\Commands\Day5;

use Illuminate\Support\Str;

class Seeds extends Map
{
    public static function fromString(string $string): self
    {
        return new self(array_map(
            fn (array $array) => Seed::fromArray($array),
            array_chunk(array_filter(explode(' ', Str::after($string, ':'))), 2)
        ));
    }
}

class Seed extends Range
{
    /**
     * @param  array<int>  $array
     */
    public static function fromArray(array $array): self
    {
        return new self(
            sourceStart: $array[0],
            sourceEnd: $array[0] + $array[1] - 1,
        );
    }
}

class Map
{
    /**
     * @param  array<Range>  $ranges
     */
    public function __construct(
        public readonly array $ranges = []
    ) {
    }

    public static function fromString(string $string): self
    {
        $lines = array_slice(array_filter(explode("\n", $string)), 1);

        $ranges = array_map(fn (string $line) => Range::fromString($line), $lines);

        usort($ranges, function ($a, $b) {
            return $a->sourceStart <=> $b->sourceStart;
        });

        // Fill in the first 'missing range'
        if ($ranges[0]->sourceStart > 0) {
            $ranges = array_merge([
                new Range(
                    sourceStart: 0,
                    sourceEnd: $ranges[0]->sourceStart - 1,
                    destinationStart: 0,
                    destinationEnd: $ranges[0]->sourceStart - 1,
                ),
            ], $ranges);
        }

        // Fill in the 'missing ranges'
        for ($i = 0; $i < count($ranges) - 1; $i++) {
            $current = $ranges[$i];
            $next = $ranges[$i + 1];

            if ($ranges[$i]->sourceEnd + 1 < $ranges[$i + 1]->sourceStart) {
                $start = $ranges[$i]->sourceEnd + 1;
                $end = $ranges[$i + 1]->sourceStart - 1;

                $ranges[] = new Range(
                    sourceStart: $start,
                    sourceEnd: $end,
                    destinationStart: $start,
                    destinationEnd: $end,
                );
            }
        }

        return new self($ranges);
    }
}

class Range
{
    public function __construct(
        public readonly int $sourceStart,
        public readonly int $sourceEnd,
        public readonly int $destinationStart = -1,
        public readonly int $destinationEnd = -1,
    ) {
    }

    public static function fromString(string $line): self
    {
        [$destination, $source, $length] = explode(' ', $line);

        $length -= 1;

        return new self(
            sourceStart: $source,
            sourceEnd: $source + $length,
            destinationStart: $destination,
            destinationEnd: $destination + $length,
        );
    }

    public function next(int $number): int
    {
        if ($number >= $this->sourceStart && $number <= $this->sourceEnd) {
            return $this->destinationStart + ($number - $this->sourceStart);
        }

        return $number;
    }
}
