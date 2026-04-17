<?php
declare(strict_types=1);

namespace App\Admin\Services;

use App\Admin\Repositories\AdminViewRepository;
use App\Admin\Exceptions\NotFoundException;

class AdminViewService
{
    public function __construct(
        private AdminViewRepository $repo,
    ) {}

    public function getDashboardStats(): array
    {
        return $this->repo->getDashboardStats();
    }

    public function getSalesReport(array $filters = []): array
    {
        return $this->repo->getSalesReport($filters);
    }

    public function getInventoryReport(array $filters = []): array
    {
        return $this->repo->getInventoryReport($filters);
    }

    public function getUserReport(array $filters = []): array
    {
        return $this->repo->getUserReport($filters);
    }

    public function getOrderStats(array $filters = []): array
    {
        return $this->repo->getOrderStats($filters);
    }

    public function getTopProducts(int $limit = 10): array
    {
        return $this->repo->getTopProducts($limit);
    }

    public function getRecentOrders(int $limit = 10): array
    {
        return $this->repo->getRecentOrders($limit);
    }

    /**
     * Generic detail view for an entity (used by admin modals)
     */
    public function getDetail(string $entity, int $id): array
    {
        $detail = $this->repo->getDetail($entity, $id);

        if ($detail === null) {
            throw new NotFoundException('Record not found');
        }

        return $detail;
    }

    /**
     * Generic list view for an entity with basic pagination + optional search
     */
    public function getList(string $entity, int $limit = 50, int $offset = 0, ?string $search = null): array
    {
        $items = $this->repo->getList($entity, $limit, $offset, $search);
        $total = $this->repo->getCount($entity, $search);

        return [
            'items'      => $items,
            'pagination' => [
                'total'  => $total,
                'limit'  => $limit,
                'offset' => $offset,
            ],
        ];
    }
}
