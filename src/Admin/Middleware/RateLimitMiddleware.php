<?php
declare(strict_types=1);

namespace App\Admin\Middleware;

use App\Core\Session;

class RateLimitMiddleware
{
    private static ?Session $session = null;

    // Defaults — can be changed at runtime via configure()
    private static int $maxRequests = 3;
    private static int $timeWindow = 60; // seconds
    private static string $baseKey = 'rate_limit';

    public static function check($key,$maxRequests = 3, $timeWindow = 60): void{
        self::configure(3, 60); // default: 3 requests per 60 seconds
        if(self::checkExecute($key)){
    //        throw new Exception("Rate limit exceeded. Try again later.");
        };

    }

    public static function configure(int $maxRequests, int $timeWindowSeconds): void
    {
        if ($maxRequests > 0) {
            self::$maxRequests = $maxRequests;
        }
        if ($timeWindowSeconds > 0) {
            self::$timeWindow = $timeWindowSeconds;
        }
    }

    /**
     * Check rate limit for the current session or a provided identifier.
     *
     * Returns true if the limit has been exceeded (caller should handle response),
     * or false if the request is allowed.
     *
     * @param string|null $identifier Optional identifier (e.g. user id or IP). If null, per-session key is used.
     * @return bool
     */
    public static function checkExecute(?string $identifier = null): bool
    {
        self::$session = Session::getInstance();
        $key = self::makeKey($identifier);
        $now = time();

        // Initialize if missing (first request in window)
        if (!self::$session->has($key)) {
            self::$session->set($key, [
                'count'      => 1,
                'start_time' => $now,
            ]);
            return false;
        }

        $rateData = self::$session->get($key);

        // Normalize stored values
        $count = isset($rateData['count']) ? (int)$rateData['count'] : 0;
        $start = isset($rateData['start_time']) ? (int)$rateData['start_time'] : $now;

        $elapsed = $now - $start;

        if ($elapsed < self::$timeWindow) {
            if ($count >= self::$maxRequests) {
                return true; // limit exceeded
            }

            // increment and persist
            $rateData['count'] = $count + 1;
            $rateData['start_time'] = $start;
            self::$session->set($key, $rateData);
            return false;
        }

        // Window expired: reset and count current request
        self::$session->set($key, [
            'count'      => 1,
            'start_time' => $now,
        ]);
        return false;
    }

    /**
     * Reset rate limit for an identifier (or current session if null).
     *
     * @param string|null $identifier
     * @return void
     */
    public static function reset(?string $identifier = null): void
    {
        self::$session = Session::getInstance();
        $key = self::makeKey($identifier);
        if (self::$session->has($key)) {
           // self::$session->remove($key);
        }
    }

    /**
     * Build the session key for storage.
     *
     * @param string|null $identifier
     * @return string
     */
    private static function makeKey(?string $identifier = null): string
    {
        if ($identifier !== null && $identifier !== '') {
            return self::$baseKey . ':' . preg_replace('/[^a-zA-Z0-9_\-]/', '_', $identifier);
        }

        return self::$baseKey;
    }

    /**
     * Convenience: emit 429 JSON and exit (caller can use this if desired).
     *
     * @param string|null $message
     * @return void
     */
    public static function emitLimitExceededResponse(?string $message = null): void
    {
        header('Content-Type: application/json; charset=utf-8');
        http_response_code(429);
        echo json_encode([
            'success' => false,
            'message' => $message ?? 'Rate limit exceeded. Try again later.',
            'data'    => null,
            'code'    => 429,
            'context' => []
        ]);
        exit;
    }
}
?>
