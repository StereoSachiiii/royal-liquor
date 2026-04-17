<?php
declare(strict_types=1);

namespace App\Admin\Middleware;

class JsonMiddleware{
    /**
 * Helper function to send JSON response and exit
 * @param array $data Response data
 * @param int $statusCode HTTP status code
 */
public static function sendResponse(array $data, int $statusCode = 200): void {
    http_response_code($statusCode);
    echo json_encode($data);
    exit;
}


}
?>
