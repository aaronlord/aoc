<?php

namespace App\Console\Commands;

use App\Console\Commands\Day10\Coordinate;
use App\Console\Commands\Day10\Grid;
use App\Console\Commands\Day10\Row;
use App\Console\Commands\Day10\Step;
use App\Console\Commands\Day10\Tile;
use Illuminate\Console\Command;
use Illuminate\Contracts\Filesystem\Filesystem;
use LogicException;

class Day10Command extends Command
{
    protected $signature = 'day:10 {--test}';

    protected $description = 'https://adventofcode.com/2023/day/10';

    public function handle(Filesystem $fs): void
    {
        $input = $fs->get($this->option('test') ? 'day10.test.txt' : 'day10.txt');

        $grid = $this->sketch($input);

        [$a, $b] = $grid->adjacent($grid->start);

        $pedometer = 1;

        $a = new Step(
            current: $a,
            next: $grid->at($a->to($grid->start))
        );

        $b = new Step(
            current: $b,
            next: $grid->at($b->to($grid->start))
        );

        do {
            $pedometer++;

            $a = new Step(
                current: $a->next,
                next: $grid->at($a->next->to($a->current->at))
            );

            $b = new Step(
                current: $b->next,
                next: $grid->at($b->next->to($b->current->at))
            );
        } while ($a->current !== $b->current);

        $this->line($pedometer);
    }

    private function sketch(string $contents): Grid
    {
        $rows = [];
        $start = null;

        foreach (explode("\n", $contents) as $y => $line) {
            if (empty($line)) {
                continue;
            }

            $row = [];

            foreach (str_split($line) as $x => $value) {
                $coordinate = new Coordinate($x, $y);

                $tile = new Tile($coordinate, $value);

                if ($tile->isStart()) {
                    $start = $tile->coordinate;
                }

                $pipe = $tile->pipe();

                if (! $pipe) {
                    continue;
                }

                $row[$x] = $pipe;
            }

            $rows[$y] = new Row($row);
        }

        if (! $start) {
            throw new LogicException('No start found');
        }

        return new Grid($rows, $start);
    }
}

namespace App\Console\Commands\Day10;

use LogicException;

class Grid
{
    /**
     * @param  array<Row>  $items
     */
    public function __construct(
        public readonly array $items,
        public Coordinate $start
    ) {
    }

    /**
     * @return array<Pipe>
     */
    public function adjacent(Coordinate $from): array
    {
        $to = [];

        foreach ($this->surrounding($from) as $pipe) {
            if (! $pipe->isConnected($from)) {
                continue;
            }

            $to[] = $pipe;
        }

        if (count($to) !== 2) {
            throw new LogicException('Start must have 2 possible moves');
        }

        return $to;
    }

    /**
     * @return array<Pipe>
     */
    public function surrounding(Coordinate $coordinate): array
    {
        return array_filter([
            $this->at(new Coordinate($coordinate->x, $coordinate->y - 1)),
            $this->at(new Coordinate($coordinate->x, $coordinate->y + 1)),
            $this->at(new Coordinate($coordinate->x - 1, $coordinate->y)),
            $this->at(new Coordinate($coordinate->x + 1, $coordinate->y)),
        ]);
    }

    public function at(Coordinate $coordinate): ?Pipe
    {
        if (! isset($this->items[$coordinate->y])) {
            return null;
        }

        if (! isset($this->items[$coordinate->y]->items[$coordinate->x])) {
            return null;
        }

        return $this->items[$coordinate->y]->items[$coordinate->x];
    }
}

class Row
{
    /**
     * @param  array<Pipe>  $items
     */
    public function __construct(
        public readonly array $items
    ) {
    }
}

class Tile
{
    public function __construct(
        public readonly Coordinate $coordinate,
        public readonly string $value
    ) {
    }

    public function pipe(): ?Pipe
    {
        return match ($this->value) {
            '|' => new Pipe(
                at: $this->coordinate,
                value: $this->value,
                entry: new Coordinate($this->coordinate->x, $this->coordinate->y - 1),
                exit: new Coordinate($this->coordinate->x, $this->coordinate->y + 1),
            ),
            '-' => new Pipe(
                at: $this->coordinate,
                value: $this->value,
                entry: new Coordinate($this->coordinate->x - 1, $this->coordinate->y),
                exit: new Coordinate($this->coordinate->x + 1, $this->coordinate->y),
            ),
            'L' => new Pipe(
                at: $this->coordinate,
                value: $this->value,
                entry: new Coordinate($this->coordinate->x, $this->coordinate->y - 1),
                exit: new Coordinate($this->coordinate->x + 1, $this->coordinate->y),
            ),
            'J' => new Pipe(
                at: $this->coordinate,
                value: $this->value,
                entry: new Coordinate($this->coordinate->x, $this->coordinate->y - 1),
                exit: new Coordinate($this->coordinate->x - 1, $this->coordinate->y),
            ),
            '7' => new Pipe(
                at: $this->coordinate,
                value: $this->value,
                entry: new Coordinate($this->coordinate->x, $this->coordinate->y + 1),
                exit: new Coordinate($this->coordinate->x - 1, $this->coordinate->y),
            ),
            'F' => new Pipe(
                at: $this->coordinate,
                value: $this->value,
                entry: new Coordinate($this->coordinate->x, $this->coordinate->y + 1),
                exit: new Coordinate($this->coordinate->x + 1, $this->coordinate->y),
            ),
            'S' => new Pipe(
                at: $this->coordinate,
                value: $this->value,
                entry: $this->coordinate,
                exit: $this->coordinate,
            ),
            default => null,
        };
    }

    public function isStart(): bool
    {
        return $this->value === 'S';
    }
}

class Pipe
{
    public function __construct(
        public readonly string $value,
        public readonly Coordinate $at,
        public readonly Coordinate $entry,
        public readonly Coordinate $exit
    ) {
    }

    public function isConnected(Coordinate $to): bool
    {
        return $this->entry->is($to) || $this->exit->is($to);
    }

    public function to(Coordinate $from): Coordinate
    {
        return $this->entry->is($from)
            ? $this->exit
            : $this->entry;
    }
}

class Step
{
    public function __construct(
        public readonly Pipe $current,
        public readonly Pipe $next,
    ) {
    }
}

class Coordinate
{
    public function __construct(
        public readonly int $x,
        public readonly int $y
    ) {
    }

    public function is(Coordinate $coordinate): bool
    {
        return $this->x === $coordinate->x
            && $this->y === $coordinate->y;
    }
}
