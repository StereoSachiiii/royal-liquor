<?php
declare(strict_types=1);

namespace App\Admin\Controllers;

use App\Admin\Services\AdminViewService;

class AdminViewController extends BaseController
{
    public function __construct(
        private AdminViewService $service,
    ) {}

    public function getDashboardStats(): array
    {
        $stats = $this->service->getDashboardStats();
        return $this->success('Dashboard stats loaded successfully', $stats);
    }

    public function getSalesReport(array $filters = []): array
    {
        $report = $this->service->getSalesReport($filters);
        return $this->success('Sales report loaded successfully', $report);
    }

    public function getInventoryReport(array $filters = []): array
    {
        $report = $this->service->getInventoryReport($filters);
        return $this->success('Inventory report loaded successfully', $report);
    }

    public function getUserReport(array $filters = []): array
    {
        $report = $this->service->getUserReport($filters);
        return $this->success('User report loaded successfully', $report);
    }

    public function getOrderStats(array $filters = []): array
    {
        $stats = $this->service->getOrderStats($filters);
        return $this->success('Order stats loaded successfully', $stats);
    }

    public function getTopProducts(int $limit = 10): array
    {
        $products = $this->service->getTopProducts($limit);
        return $this->success('Top products loaded successfully', $products);
    }

    public function getRecentOrders(int $limit = 10): array
    {
        $orders = $this->service->getRecentOrders($limit);
        return $this->success('Recent orders loaded successfully', $orders);
    }

    /**
     * Generic detail view endpoint used by admin-views.php
     */
    public function getDetail(string $entity, int $id): array
    {
        $detail = $this->service->getDetail($entity, $id);
        return $this->success(ucfirst($entity) . ' detail loaded successfully', $detail);
    }

    /**
     * Generic list view endpoint used by admin-views.php
     */
    public function getList(string $entity, int $limit = 50, int $offset = 0, ?string $search = null): array
    {
        $list = $this->service->getList($entity, $limit, $offset, $search);
        return $this->success(ucfirst($entity) . ' list loaded successfully', $list);
    }
}
