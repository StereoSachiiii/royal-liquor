<?php
declare(strict_types=1);

namespace App\Admin\Controllers;

use App\Admin\Services\AIRecommendationService;
use App\Core\Session;

class RecommendationController extends BaseController
{
    private AIRecommendationService $service;

    public function __construct(AIRecommendationService $service)
    {
        $this->service = $service;
    }

    public function getForYou(): void
    {
        try {
            $userId = Session::getInstance()->getUserId(); // Can be null for guests
            
            // Limit to 4 cards for the homepage UI row
            $recommendations = $this->service->getRecommendationsForUser($userId, 4);
            
            $this->jsonResponse([
                'success' => true,
                'data' => $recommendations
            ]);
        } catch (\Exception $e) {
            $this->jsonResponse(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }
}
