<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Contracts\Filesystem\Filesystem;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class Day6Command extends Command
{
    protected $signature = 'day:6 {--test}';

    protected $description = 'https://adventofcode.com/2023/day/6';

    public function handle(Filesystem $fs): void
    {
        $contents = $fs->get($this->option('test') ? 'day6.test.txt' : 'day6.txt');
        $contents = explode("\n", $contents);

        $result = $this->races($contents)
            ->map($this->count(...))
            ->reduce(fn (int $carry, int $count) => $carry * $count, 1);

        $this->line($result);
    }

    /**
     * @param  array<string>  $contents
     * @return Collection<array<int>>
     */
    private function races(array $contents): Collection
    {
        $time = $this->values($contents[0]);
        $distance = $this->values($contents[1]);

        return $time->zip($distance);
    }

    /**
     * @param  Collection<int>  $race
     */
    private function count(Collection $race): int
    {
        $time = (int) $race[0];
        $distance = (int) $race[1];

        $count = 0;

        for ($holdTime = 1; $holdTime < $time; $holdTime++) {
            $travelTime = $time - $holdTime;
            $distanceTravelled = $holdTime * $travelTime;

            if ($distanceTravelled > $distance) {
                $count++;
            }
        }

        return $count;
    }

    /**
     * @return Collection<string>
     */
    private function values(string $line): Collection
    {
        $string = Str::of($line)
            ->after(':')
            ->replace(' ', '')
            ->toString();

        return collect([$string]);
    }
}
