<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Contracts\Filesystem\Filesystem;
use Illuminate\Support\Collection;

class Day3Command extends Command
{
    protected $signature = 'day:3';

    protected $description = 'https://adventofcode.com/2023/day/3';

    public function handle(Filesystem $fs): void
    {
        $contents = $fs->get('day3.txt');
        $lines = explode("\n", $contents);

        $schematic = (new Schematic($lines))
            ->reject(fn (string $line): bool => empty($line))
            ->map($this->toLine(...));

        $numbers = $schematic->map($this->numbers(...))
            ->pipeInto(Numbers::class);

        $sum = $schematic->reduce(
            fn (int $carry, Line $line): int => $carry + $line->reduce(
                fn (int $carry, Column $column): int => $carry + $this->gearRatio($numbers, $column),
                0
            ),
            0
        );

        $this->line($sum);
    }

    private function toLine(string $line, int $y): Line
    {
        $line = new Line(str_split($line));

        return $line->map(fn ($value, $x) => new Column($value, new Coordinate($x, $y)));
    }

    private function numbers(Line $line): Numbers
    {
        $numbers = new Numbers();
        $number = null;

        foreach ($line as $column) {
            if ($column->isNumber()) {
                $number ??= new NumberBuilder();

                $number->add($column);

                continue;
            }

            if ($number) {
                $numbers->add($number->build());
            }

            $number = null;
        }

        if ($number) {
            $numbers->add($number->build());
        }

        return $numbers;
    }

    private function gearRatio(Numbers $numbers, Column $column): int
    {
        if (! $column->isGear()) {
            return 0;
        }

        $gears = $numbers->adjacentTo($column->coordinate);

        if ($gears->count() !== 2) {
            return 0;
        }

        return $gears[0]->value * $gears[1]->value;
    }
}

class Schematic extends Collection
{
}

class Line extends Collection
{
}

class Column
{
    public function __construct(
        public readonly string $value,
        public readonly Coordinate $coordinate,
    ) {
    }

    public function isGear(): bool
    {
        return $this->value === '*';
    }

    public function isNumber(): bool
    {
        return is_numeric($this->value);
    }
}

class NumberBuilder
{
    protected $parts = [];

    protected $coordinates = [];

    public function add(Column $column): void
    {
        $this->parts[] = $column->value;
        $this->coordinates[] = $column->coordinate;
    }

    public function build(): Number
    {
        return new Number(
            (int) implode('', $this->parts),
            $this->coordinates,
        );
    }
}

class Numbers extends Collection
{
    public function adjacentTo(Coordinate $coordinate): Collection
    {
        return $this->slice($coordinate->y - 1, 3)
            ->flatten()
            ->filter(fn (Number $number): bool => $number->adjacentTo($coordinate))
            ->values();
    }
}

class Number
{
    public function __construct(
        public readonly int $value,
        public readonly array $coordinates,
    ) {
    }

    public function adjacentTo(Coordinate $coordinate): bool
    {
        return collect($this->coordinates)
            ->filter(fn (Coordinate $c) => $c->adjacentTo($coordinate))
            ->isNotEmpty();
    }
}

class Coordinate
{
    public function __construct(
        public readonly int $x,
        public readonly int $y,
    ) {
    }

    public function adjacentTo(Coordinate $coordinate): bool
    {
        return abs($this->x - $coordinate->x) <= 1
            && abs($this->y - $coordinate->y) <= 1;
    }
}
