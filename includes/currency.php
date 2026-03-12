<?php
/**
 * Currency Helper Functions
 * Converts USD to INR and formats currency display
 */

// Conversion rate: 1 USD = 83 INR (approximate)
define('USD_TO_INR_RATE', 1.00);

/**
 * Convert USD amount to INR
 * @param float $usdAmount Amount in USD
 * @return float Amount in INR
 */
function usdToInr($usdAmount) {
    return $usdAmount * USD_TO_INR_RATE;
}

/**
 * Format currency in INR
 * @param float $amount Amount in USD (will be converted to INR)
 * @param int $decimals Number of decimal places
 * @return string Formatted currency string
 */
function formatCurrency($amount, $decimals = 2) {
    $inrAmount = usdToInr($amount);
    return '₹' . number_format($inrAmount, $decimals);
}

/**
 * Format currency with symbol only (no conversion, assumes already in INR)
 * @param float $amount Amount in INR
 * @param int $decimals Number of decimal places
 * @return string Formatted currency string
 */
function formatInr($amount, $decimals = 2) {
    return '₹' . number_format($amount, $decimals);
}

