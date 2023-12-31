<?php

namespace App\Console\Commands;

use App\Console\Commands\Day8\Instructions;
use App\Console\Commands\Day8\Node;
use Illuminate\Console\Command;
use Illuminate\Contracts\Filesystem\Filesystem;
use Illuminate\Support\Str;

class Day8Command extends Command
{
    protected $signature = 'day:8 {--test}';

    protected $description = 'https://adventofcode.com/2023/day/8';

    public function handle(Filesystem $fs): void
    {
        $contents = $fs->get($this->option('test') ? 'day8.test.txt' : 'day8.txt');
        $contents = explode("\n", $contents);

        $instructions = Instructions::fromString(array_shift($contents));

        $nodes = collect($contents)
            ->reject(fn (string $line): bool => empty($line))
            ->map(Node::fromString(...))
            ->values()
            ->keyBy(fn (Node $node) => $node->name);

        $steps = $nodes
            ->filter(fn (Node $node) => Str::endsWith($node->name, 'A'))
            ->map(function (Node $node) use ($nodes, $instructions): int {
                $steps = 0;

                do {
                    $node = $nodes->get($node->get($instructions->get()));

                    $steps++;
                } while (! Str::endsWith($node->name, 'Z'));

                return $steps;
            })
            ->values()
            ->toArray();

        $this->line($this->leastCommonMultiple($steps));
    }

    /**
     * @param  array<int>  $numbers
     */
    private function leastCommonMultiple(array $numbers): int
    {
        $lcm = $numbers[0];

        for ($i = 1; $i < count($numbers); $i++) {
            $lcm = ($numbers[$i] * $lcm) / $this->greatestCommonDivisor($numbers[$i], $lcm);
        }

        return $lcm;
    }

    private function greatestCommonDivisor(int $a, int $b): int
    {
        if ($b === 0) {
            return $a;
        }

        return $this->greatestCommonDivisor($b, $a % $b);
    }
}

namespace App\Console\Commands\Day8;

use Illuminate\Support\Collection;

class Instructions
{
    private readonly int $count;

    /**
     * @param  array<string>  $instructions
     */
    public function __construct(
        private readonly array $instructions,
        private int $index = 0
    ) {
        $this->count = count($instructions);
    }

    public static function fromString(string $string): self
    {
        $instructions = str_split($string);

        return new self($instructions);
    }

    public function get(): string
    {
        if ($this->index >= $this->count) {
            $this->index = 0;
        }

        return $this->instructions[$this->index++];
    }
}

class Node
{
    public function __construct(
        public readonly string $name,
        public readonly Elements $elements
    ) {
    }

    public static function fromString(string $string): self
    {
        [$name, $elements] = explode('=', $string);

        return new self(
            trim($name),
            Elements::fromString($elements)
        );
    }

    public function get(string $key): string
    {
        return $this->elements->get($key);
    }
}

class Elements extends Collection
{
    public static function fromString(string $string): self
    {
        $elements = array_map(
            fn (string $s) => trim($s, '(), '),
            explode(' ', trim($string))
        );

        return new self([
            'L' => $elements[0],
            'R' => $elements[1],
        ]);
    }
}
