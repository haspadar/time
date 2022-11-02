<?php

namespace WhatTime;

class Filter
{
    public static function get($unfiltered): string
    {
        return htmlentities(trim($unfiltered));
    }
}