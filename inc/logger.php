<?php
/**
 * Logger utilities for Crypto Sekhyab theme
 * - File-based logging with rotation
 * - Admin-only readers
 *
 * @package CryptoSekhyab
 */

if (!defined('ABSPATH')) {
    exit;
}

if (!function_exists('crypto_sekhyab_logger_enabled')) {
    function crypto_sekhyab_logger_enabled() {
        $enabled = get_option('crypto_sekhyab_logging_enabled', 1);
        return (bool) $enabled;
    }
}

if (!function_exists('crypto_sekhyab_get_log_level')) {
    function crypto_sekhyab_get_log_level() {
        $level = get_option('crypto_sekhyab_logging_level', 'INFO');
        $level = strtoupper($level);
        $valid = array('DEBUG', 'INFO', 'WARNING', 'ERROR');
        return in_array($level, $valid, true) ? $level : 'INFO';
    }
}

if (!function_exists('crypto_sekhyab_level_to_int')) {
    function crypto_sekhyab_level_to_int($level) {
        switch (strtoupper($level)) {
            case 'DEBUG': return 10;
            case 'INFO': return 20;
            case 'WARNING': return 30;
            case 'ERROR': return 40;
        }
        return 20;
    }
}

if (!function_exists('crypto_sekhyab_get_log_file_path')) {
    function crypto_sekhyab_get_log_file_path() {
        $upload_dir = wp_upload_dir();
        $dir = trailingslashit($upload_dir['basedir']) . 'crypto-sekhyab/logs';
        if (!file_exists($dir)) {
            wp_mkdir_p($dir);
        }
        return $dir . '/crypto-sekhyab.log';
    }
}

if (!function_exists('crypto_sekhyab_rotate_logs')) {
    function crypto_sekhyab_rotate_logs() {
        $path = crypto_sekhyab_get_log_file_path();
        $max_size_kb = intval(get_option('crypto_sekhyab_log_max_size', 2048)); // KB, default ~2MB
        if (file_exists($path)) {
            $size_kb = filesize($path) / 1024;
            if ($size_kb > $max_size_kb) {
                $timestamp = date('Ymd_His');
                $rotated = dirname($path) . '/crypto-sekhyab-' . $timestamp . '.log';
                @rename($path, $rotated);
                @file_put_contents($path, '');
            }
        }
    }
}

if (!function_exists('crypto_sekhyab_log')) {
    function crypto_sekhyab_log($level, $message, $context = array()) {
        if (!crypto_sekhyab_logger_enabled()) {
            return;
        }
        $current_level_int = crypto_sekhyab_level_to_int(crypto_sekhyab_get_log_level());
        $level_int = crypto_sekhyab_level_to_int($level);
        if ($level_int < $current_level_int) {
            return;
        }
        $path = crypto_sekhyab_get_log_file_path();
        crypto_sekhyab_rotate_logs();
        $entry = array(
            'time' => date('Y-m-d H:i:s'),
            'level' => strtoupper($level),
            'message' => is_string($message) ? $message : wp_json_encode($message),
            'context' => $context,
            'ip' => isset($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : 'CLI',
        );
        $line = '[' . $entry['time'] . '] ' . $entry['level'] . ' ' . $entry['message'];
        if (!empty($context)) {
            $line .= ' | ' . wp_json_encode($context, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        }
        $line .= PHP_EOL;
        @file_put_contents($path, $line, FILE_APPEND | LOCK_EX);
    }
}

if (!function_exists('crypto_sekhyab_read_logs')) {
    function crypto_sekhyab_read_logs($lines = 500) {
        $path = crypto_sekhyab_get_log_file_path();
        if (!file_exists($path)) {
            return array();
        }
        // Read last N lines efficiently
        $f = @fopen($path, 'r');
        if (!$f) return array();
        $buffer = '';
        $chunk_size = 8192;
        $pos = -1;
        $line_count = 0;
        $stat = fstat($f);
        $file_size = $stat['size'];
        $seek = 0;
        while ($line_count <= $lines && $seek < $file_size) {
            $seek += $chunk_size;
            fseek($f, -$seek, SEEK_END);
            $chunk = fread($f, $chunk_size);
            $buffer = $chunk . $buffer;
            $line_count = substr_count($buffer, "\n");
        }
        fclose($f);
        $all_lines = explode("\n", trim($buffer));
        return array_slice($all_lines, -$lines);
    }
}

if (!function_exists('crypto_sekhyab_clear_logs')) {
    function crypto_sekhyab_clear_logs() {
        $path = crypto_sekhyab_get_log_file_path();
        @file_put_contents($path, '');
        return true;
    }
}
