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

        $sum = (new Schematic($lines))
            ->reject(fn (string $line): bool => empty($line))
            ->map($this->toLine(...))
            ->pipe(fn (Schematic $schematic): int => $schematic->reduce(
                fn (int $carry, Line $line): int => $carry + $this->sum($schematic, $line),
                0
            ));

        $this->line($sum);
    }

    private function toLine(string $line, int $y): Line
    {
        $line = new Line(str_split($line));

        return $line->map(fn ($value, $x) => new Column($value, new Coordinate($x, $y)));
    }

    private function sum(Schematic $schematic, Line $line): int
    {
        $numbers = collect();
        $number = null;

        foreach ($line as $column) {
            if ($column->isNumber()) {
                $number ??= new NumberBuilder();

                $number->add($column);

                continue;
            }

            if ($number) {
                $number = $number->build();

                if ($number->isAdjacentToSymbol($schematic)) {
                    $numbers->add($number);
                }
            }

            $number = null;
        }

        if ($number) {
            $number = $number->build();

            if ($number->isAdjacentToSymbol($schematic)) {
                $numbers->add($number);
            }
        }

        return $numbers->sum('value');
    }
}

class Schematic extends Collection
{
    public function adjacentColumns(Coordinate $coordinate): Collection
    {
        return collect([
            $this->get($coordinate->y - 1)?->get($coordinate->x - 1),
            $this->get($coordinate->y - 1)?->get($coordinate->x),
            $this->get($coordinate->y - 1)?->get($coordinate->x + 1),
            $this->get($coordinate->y)?->get($coordinate->x - 1),
            $this->get($coordinate->y)?->get($coordinate->x + 1),
            $this->get($coordinate->y + 1)?->get($coordinate->x - 1),
            $this->get($coordinate->y + 1)?->get($coordinate->x),
            $this->get($coordinate->y + 1)?->get($coordinate->x + 1),
        ])->filter();
    }
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

    public function isPeriod(): bool
    {
        return $this->value === '.';
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

class Number
{
    public function __construct(
        public readonly int $value,
        public readonly array $coordinates,
    ) {
    }

    public function isAdjacentToSymbol(Schematic $schematic): bool
    {
        foreach ($this->coordinates as $coordinate) {
            $columns = $schematic->adjacentColumns($coordinate);

            foreach ($columns as $column) {
                if ($column->isPeriod() || $column->isNumber()) {
                    continue;
                }

                return true;
            }
        }

        return false;
    }
}

class Coordinate
{
    public function __construct(
        public readonly int $x,
        public readonly int $y,
    ) {
    }
}
