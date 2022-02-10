<?php

/**
 * Outputs formatted and filtered entries from the laravel log file.
 * Usage: php logv.php [OPTION]
 * Options:
 * -n[NUM]    output the last NUM log entries, default is 1. Use -n0 for entire file.
 * -f         continuously monnitor the file for changes
 *
 * Author: Ed Shortt <edjash@gmail.com>
 * Date: 10/02/2022
 */

class LogView
{
    private string $file;
    private int $numerrors = 1;
    private string $lines;
    private bool $watch = false;

    public function __construct(array $options = [])
    {
        $this->numerrors = $options['n'] ?? 1;
        $this->setLines();
        $this->execute();
        $this->watch = isset($options['f']);
        if ($this->watch) {
            $this->numerrors = 1;
            $this->startWatch();
        }
    }

    private function startWatch()
    {
        $this->output('Watching file for changes..');
        $mtime = filemtime($this->file);
        while (true) {
            sleep(1);
            clearstatcache();
            $ntime = filemtime($this->file);
            if ($mtime != $ntime) {
                $mtime = $ntime;
                $this->setLines();
                $this->execute(false);
            }
        }
    }

    private function setLines()
    {
        $this->file = __DIR__ . '/storage/logs/laravel.log';
        if (!is_file($this->file)) {
            die("Log file '$this->file' not found.\n");
        }

        $cmd = "cat $this->file";
        if ($this->numerrors) {
            $n = $this->numerrors * 200;
            $cmd = "tail -n$n $this->file";
        }
        $this->lines = `$cmd`;
    }

    private function execute($showHeader = true)
    {
        $inverted = "\e[7m";
        $timeColor = "\e[97m{$inverted}";
        $stackTraceTitleColor = "\e[90m{$inverted}";
        $mainErrorColor = "\e[37m";

        if ($showHeader) {
            if (!$this->numerrors) {
                $this->output("Show all entries from file $this->file");
            } elseif ($this->numerrors == 1) {
                $this->output("Showing the last entry from $this->file\n");
            } else {
                $this->output("Showing the last $this->numerrors entries from $this->file\n");
            }
        }

        $entries = $this->getEntries();
        foreach ($entries as $entry) {
            $timeString = $this->getTimeString($entry);
            $mainError  = $this->getMainError($entry);
            $stackArray = $this->getFilteredStackTrace($entry);
            $this->output($timeString, $timeColor);
            $this->output($mainError, $mainErrorColor);

            if (!$count = count($stackArray)) {
                $this->output("");
                continue;
            }
            $this->output("[stacktrace - filtered]", $stackTraceTitleColor);
            foreach ($stackArray as $index => $item) {
                $this->output("Trace Number: {$item['traceNumber']}");
                $this->output("File: {$item['file']}");
                $this->output("Line: {$item['line']}");
                if ($item['method']) {
                    $this->output("Method: {$item['method']}");
                }
                $this->output(($index < $count - 1) ? '----' : "");
            }
        }

        $this->lines = "";
    }

    private function output(string $string, $color = "\e[39m")
    {
        echo $color . $string . "\e[0m\n";
    }

    private function getEntries(): array
    {
        //Get log entries starting with timestamp in format [2022-02-09 12:08:02]
        $entries = preg_split('/(?=\[\d{4}\-\d{2}\-\d{2} \d{2}\:\d{2}\:\d{2}\])/', $this->lines);
        if (!count($entries)) {
            return [];
        }
        return array_slice($entries, -$this->numerrors);
    }

    private function getTimeString(string $entry): string
    {
        $matches = [];
        if (preg_match('/\[\d{4}\-\d{2}\-\d{2} \d{2}\:\d{2}\:\d{2}\]/', $entry, $matches)) {
            return $matches[0];
        }
        return '';
    }

    private function getMainError(string $entry): string
    {
        $matches = [];
        if (preg_match('/\[\d{4}\-\d{2}\-\d{2} \d{2}\:\d{2}\:\d{2}\] (.+)/', $entry, $matches)) {
            return $matches[1];
        }
        return '';
    }

    private function getFilteredStackTrace(string $entry): array
    {
        $stackTrace = [];
        $lines = explode("\n", $entry);
        $found = false;
        foreach ($lines as $line) {
            $line = trim($line);
            if ($line === '[stacktrace]') {
                $found = true;
                continue;
            }
            if (!$found) {
                continue;
            }

            $lineArr = explode(":", $line);
            $matches = [];
            preg_match('/(#\d+)\s(.+)\((.*?)\)/', $lineArr[0], $matches);
            if (count($matches) != 4) {
                continue;
            }
            $file = str_replace(__DIR__, '', $matches[2]);
            $method = trim($lineArr[1] ?? '');
            if (
                !preg_match('/^\/app\//', $file)
            ) {
                continue;
            }

            $stackTrace[$file . '@' . $matches[3]] = [
                "file" => $file,
                "line" => $matches[3],
                "method" => $method,
                "traceNumber" => $matches[1],
            ];
        }
        return array_values($stackTrace);
    }
}

new LogView(getopt("n:f"));
