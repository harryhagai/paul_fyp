<?php

if (!function_exists('format_money_short')) {
    /**
     * Format money into compact suffix style: K, M, B.
     */
    function format_money_short(float|int|string|null $amount, int $precision = 1, string $currency = 'Tsh'): string
    {
        return trim($currency . ' ' . format_number_short($amount, $precision));
    }
}

if (!function_exists('format_number_short')) {
    /**
     * Format large number into compact suffix style: K, M, B.
     */
    function format_number_short(float|int|string|null $amount, int $precision = 1): string
    {
        $value = (float) ($amount ?? 0);
        $abs = abs($value);

        if ($abs >= 1000000000) {
            $short = round($value / 1000000000, $precision);
            $suffix = 'B';
        } elseif ($abs >= 1000000) {
            $short = round($value / 1000000, $precision);
            $suffix = 'M';
        } elseif ($abs >= 1000) {
            $short = round($value / 1000, $precision);
            $suffix = 'K';
        } else {
            return number_format($value, 0);
        }

        $formatted = number_format($short, $precision, '.', '');
        $formatted = rtrim(rtrim($formatted, '0'), '.');

        return $formatted . $suffix;
    }
}
