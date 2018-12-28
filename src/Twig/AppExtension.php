<?php

namespace App\Twig;

use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;
use Twig\TwigTest;

class AppExtension extends AbstractExtension
{
    public function getFilters()
    {
        return [
            new TwigFilter('timeago', array($this, 'timeAgo')),
            new TwigFilter('linkify', array($this, 'linkify')),
        ];
    }

    public function getTests()
    {
        return [
            'instanceof' => new TwigTest('instanceof', [$this, 'isInstanceof'])
        ];
    }

    public function timeAgo($datetime)
    {
        $time = time() - strtotime($datetime->format('Y-m-d H:i:s'));

        $units = [
            31536000 => 'year',
            2592000 => 'month',
            604800 => 'week',
            86400 => 'day',
            3600 => 'hour',
            60 => 'minute',
            1 => 'second'
        ];

        foreach ($units as $unit => $val) {
            if ($time < $unit) continue;
            $numberOfUnits = floor($time / $unit);
            return ($val == 'second') ? 'a few seconds ago' :
                (($numberOfUnits > 1) ? $numberOfUnits : 'a')
                . ' ' . $val . (($numberOfUnits > 1) ? 's' : '') . ' ago';
        }
    }

    public function linkify($str)
    {
        $attributes = [];
        $attrs = '';
        foreach ($attributes as $attribute => $value) {
            $attrs .= " {$attribute}=\"{$value}\"";
        }
        $str = ' ' . $str;
        $str = preg_replace(
            '`([^"=\'>])((http|https|ftp)://[^\s<]+[^\s<\.)])`i',
            '$1<a href="$2"'.$attrs.'>$2</a>',
            $str
        );
        $str = substr($str, 1);

        return $str;
    }

    public function isInstanceof($var, $instance)
    {
        return $var instanceof $instance;
    }
}