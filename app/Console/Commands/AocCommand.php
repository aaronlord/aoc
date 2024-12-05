<?php

namespace App\Console\Commands;

use App\Aoc\SolutionInterface;
use Illuminate\Console\Command;
use RuntimeException;

class AocCommand extends Command
{
    protected $signature = 'app:aoc {day} {--test}';

    protected $description = 'Run the Advent of Code day';

    public function handle(): int
    {
        $day = $this->argument('day');
        $isTest = $this->option('test');

        $inputPath = storage_path(sprintf(
            'app/private/day%s%s.txt',
            $day,
            $isTest ? '_test' : ''
        ));

        throw_unless(
            file_exists($inputPath),
            new RuntimeException("Input file not found: {$inputPath}")
        );

        $class = "App\\Aoc\\Day{$this->argument('day')}\\Solution";

        throw_unless(
            class_exists($class),
            new RuntimeException("Solution not found: {$class}")
        );

        $solution = app($class);

        throw_unless(
            $solution instanceof SolutionInterface,
            new RuntimeException('Solution must implement SolutionInterface')
        );

        $this->info($solution->run($inputPath));

        return self::SUCCESS;
    }
}
