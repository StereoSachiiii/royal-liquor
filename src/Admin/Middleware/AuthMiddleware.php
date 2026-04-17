<?php
declare(strict_types=1);

namespace App\Admin\Middleware;

use App\Core\Session;


class AuthMiddleware {

    /**
     * Helper function to check authentication
     * @return int User ID
     */
    public static function requireAuth(): int {
        $session = Session::getInstance();
        
        if (!$session->isLoggedIn()) {
            http_response_code(401);
            echo json_encode([
                'success' => false,
                'message' => 'Unauthorized - Please login',
                'code' => 401
            ]);
            exit;
        }
        
        return (int)$session->get('user_id');
    }

    /**
     * Helper function to check admin role
     * @return int User ID
     */
    public static function requireAdmin(): int {
        $session = Session::getInstance();
        
        if (!$session->isLoggedIn()) {
            http_response_code(401);
            echo json_encode([
                'success' => false,
                'message' => 'Unauthorized - Please login',
                'code' => 401
            ]);
            exit;
        }
        
        if (!$session->isAdmin()) {
            http_response_code(403);
            echo json_encode([
                'success' => false,
                'message' => 'Forbidden - Admin access required',
                'code' => 403
            ]);
            exit;
        }
        
        return (int)$session->get('user_id');
    }

    /**
     * Helper function to send JSON response and exit
     * @param array $data Response data
     * @param int $statusCode HTTP status code
     */
    public static function sendResponse(array $data, int $statusCode = 200): void {
        header('Content-Type: application/json');
        http_response_code($statusCode);
        echo json_encode($data);
        exit;
    }

}
?>
