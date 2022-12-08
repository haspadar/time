<?php

namespace WhatTime;

class ExecutionTime
{
    private float $startTime;
    private float $endTime;

    public function start()
    {
        $this->startTime = microtime(true);
    }

    public function end()
    {
        $this->endTime = microtime(true);
    }

    public function get(): string
    {
        $seconds = ceil($this->endTime - $this->startTime);
        $daysCount = intval($seconds / 86400);
        $hoursCount = intval(($seconds - $daysCount * 86400) / 3600);
        $minutesCount = intval(($seconds - $daysCount * 86400 - $hoursCount * 3600) / 60);
        $secondsCount = intval($seconds - $daysCount * 86400 - $hoursCount * 3600 - $minutesCount * 60);
        $parts = [];
        if ($daysCount > 0) {
            $parts[] = $daysCount . ' days';
        }

        if ($hoursCount > 0) {
            $parts[] = $hoursCount . ' hours';
        }

        if ($minutesCount > 0) {
            $parts[] = $minutesCount . ' minutes';
        }

        $parts[] = $secondsCount . ' seconds';

        return implode(', ', $parts);
    }
}