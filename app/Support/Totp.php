<?php

namespace App\Support;

class Totp
{
    private const BASE32 = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ234567';

    public static function secret(int $length = 20): string
    {
        $bytes = random_bytes($length);
        $bits = '';
        foreach (str_split($bytes) as $byte) {
            $bits .= str_pad(decbin(ord($byte)), 8, '0', STR_PAD_LEFT);
        }

        $secret = '';
        foreach (str_split($bits, 5) as $chunk) {
            $secret .= self::BASE32[bindec(str_pad($chunk, 5, '0'))];
        }

        return $secret;
    }

    public static function verify(string $secret, string $code, ?int $timestamp = null): bool
    {
        if (! preg_match('/^\d{6}$/', $code)) {
            return false;
        }

        $key = self::decode($secret);
        $time = intdiv($timestamp ?? time(), 30);

        foreach ([-1, 0, 1] as $window) {
            $counter = $time + $window;
            $message = pack('N2', $counter >> 32, $counter & 0xffffffff);
            $hash = hash_hmac('sha1', $message, $key, true);
            $offset = ord($hash[19]) & 0x0f;
            $binary = ((ord($hash[$offset]) & 0x7f) << 24)
                | ((ord($hash[$offset + 1]) & 0xff) << 16)
                | ((ord($hash[$offset + 2]) & 0xff) << 8)
                | (ord($hash[$offset + 3]) & 0xff);

            if (hash_equals(str_pad((string) ($binary % 1_000_000), 6, '0', STR_PAD_LEFT), $code)) {
                return true;
            }
        }

        return false;
    }

    private static function decode(string $secret): string
    {
        $secret = strtoupper(rtrim($secret, '='));
        $bits = '';
        foreach (str_split($secret) as $character) {
            $value = strpos(self::BASE32, $character);
            if ($value === false) {
                throw new \InvalidArgumentException('Invalid authenticator secret.');
            }
            $bits .= str_pad(decbin($value), 5, '0', STR_PAD_LEFT);
        }

        $bytes = '';
        foreach (str_split($bits, 8) as $chunk) {
            if (strlen($chunk) === 8) {
                $bytes .= chr(bindec($chunk));
            }
        }

        return $bytes;
    }
}
