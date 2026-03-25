<?php

namespace FlowUI\Core;

class Performance
{
    private static array $timers = [];
    private static array $counters = [];
    private static array $marks = [];

    public static function start(string $label): void
    {
        self::$timers[$label] = microtime(true);
    }

    public static function end(string $label): ?float
    {
        if (!isset(self::$timers[$label])) {
            return null;
        }

        $elapsed = microtime(true) - self::$timers[$label];
        unset(self::$timers[$label]);
        
        return $elapsed;
    }

    public static function measure(string $label): ?float
    {
        if (!isset(self::$timers[$label])) {
            return null;
        }

        return microtime(true) - self::$timers[$label];
    }

    public static function mark(string $label, $data = null): void
    {
        self::$marks[] = [
            'label' => $label,
            'time' => microtime(true),
            'memory' => memory_get_usage(true),
            'data' => $data,
        ];
    }

    public static function increment(string $counter, int $amount = 1): void
    {
        if (!isset(self::$counters[$counter])) {
            self::$counters[$counter] = 0;
        }
        
        self::$counters[$counter] += $amount;
    }

    public static function getCounter(string $counter): int
    {
        return self::$counters[$counter] ?? 0;
    }

    public static function getMarks(): array
    {
        return self::$marks;
    }

    public static function getCounters(): array
    {
        return self::$counters;
    }

    public static function getReport(): array
    {
        $report = [
            'marks' => self::$marks,
            'counters' => self::$counters,
            'memory' => [
                'current' => memory_get_usage(true),
                'peak' => memory_get_peak_usage(true),
                'limit' => ini_get('memory_limit'),
            ],
            'timers' => [],
        ];

        // Add active timers
        foreach (self::$timers as $label => $start) {
            $report['timers'][$label] = [
                'running' => true,
                'elapsed' => microtime(true) - $start,
            ];
        }

        return $report;
    }

    public static function reset(): void
    {
        self::$timers = [];
        self::$counters = [];
        self::$marks = [];
    }

    public static function formatBytes(int $bytes, int $precision = 2): string
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        
        for ($i = 0; $bytes > 1024 && $i < count($units) - 1; $i++) {
            $bytes /= 1024;
        }
        
        return round($bytes, $precision) . ' ' . $units[$i];
    }

    public static function formatTime(float $seconds, int $precision = 3): string
    {
        if ($seconds < 0.001) {
            return round($seconds * 1000000, $precision) . ' μs';
        } elseif ($seconds < 1) {
            return round($seconds * 1000, $precision) . ' ms';
        } else {
            return round($seconds, $precision) . ' s';
        }
    }
}
