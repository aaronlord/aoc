<?php

namespace App\Console\Commands;

use App\Console\Commands\Day7\Hand;
use Illuminate\Console\Command;
use Illuminate\Contracts\Filesystem\Filesystem;

class Day7Command extends Command
{
    protected $signature = 'day:7 {--test}';

    protected $description = 'https://adventofcode.com/2023/day/7';

    public function handle(Filesystem $fs): void
    {
        $contents = $fs->get($this->option('test') ? 'day7.test.txt' : 'day7.txt');
        $contents = explode("\n", $contents);

        $total = collect($contents)
            ->reject(fn (string $line): bool => empty($line))
            ->map(Hand::fromString(...))
            ->sort(fn (Hand $a, Hand $b): int => $a->beats($b) ? 1 : -1)
            ->values()
            ->reduce($this->winnings(...), 0);

        $this->line($total);
    }

    private function winnings(int $carry, Hand $hand, int $index): int
    {
        return $carry + ($index + 1) * $hand->bid;
    }
}

namespace App\Console\Commands\Day7;

use LogicException;

class Hand
{
    const FIVE_OF_A_KIND = 6;
    const FOUR_OF_A_KIND = 5;
    const FULL_HOUSE = 4;
    const THREE_OF_A_KIND = 3;
    const TWO_PAIR = 2;
    const ONE_PAIR = 1;
    const HIGH_CARD = 0;

    const CARDS = [
        'A' => 12,
        'K' => 11,
        'Q' => 10,
        'T' => 9,
        9 => 8,
        8 => 7,
        7 => 6,
        6 => 5,
        5 => 4,
        4 => 3,
        3 => 2,
        2 => 1,
        'J' => 0,
    ];

    /**
     * @param array<string|int> $cards
     */
    private function __construct(
        public readonly array $cards,
        public readonly string $bid
    ) {
    }

    public static function fromString(string $line): self
    {
        [$cards, $bid] = explode(' ', $line);

        return new self(str_split($cards), $bid);
    }

    public function beats(self $other): bool
    {
        return match ($this->type() <=> $other->type()) {
            1 => true,
            -1 => false,
            0 => $this->tiebreaker($other),
        };
    }

    private function tiebreaker(self $other): bool
    {
        for ($i = 0; $i < count($this->cards); $i++) {
            $a = $this->cards[$i];
            $b = $other->cards[$i];

            if ($a === $b) {
                continue;
            }

            return self::CARDS[$a] > self::CARDS[$b];
        }

        return false;
    }

    private function type(): int
    {
        $counts = array_count_values($this->cards);

        unset($counts['J']);

        sort($counts);

        return match ($counts) {
            [5], [4], [3], [2], [1], [] => self::FIVE_OF_A_KIND,
            [1, 4], [1, 3], [1, 2], [1, 1] => self::FOUR_OF_A_KIND,
            [2, 3], [2, 2] => self::FULL_HOUSE,
            [1, 1, 3], [1, 1, 2], [1, 1, 1] => self::THREE_OF_A_KIND,
            [1, 2, 2], [1, 1, 2] => self::TWO_PAIR,
            [1, 1, 1, 2], [1, 1, 1, 1] => self::ONE_PAIR,
            [1, 1, 1, 1, 1] => self::HIGH_CARD,
            default => throw new LogicException('Invalid hand'),
        };
    }
}
