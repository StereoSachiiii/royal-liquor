<?php
declare(strict_types=1);

namespace App\Admin\Controllers;

use App\Admin\Services\StockService;

use App\Core\Session;


class StockController extends BaseController
{
    public function __construct(
        private StockService $service,
        private Session $session,
    ) {}

    // ORDER-RELATED OPERATIONS
    public function reserveStock(int $orderId): array
    {
        return $this->handle(function () use ($orderId) {
            $this->service->reserveStock($orderId);
            $this->logStockOperation('reserve', "Reserved stock for order {$orderId}");
            return $this->success('Stock reserved for order');
        });
    }

    public function confirmPayment(int $orderId): array
    {
        return $this->handle(function () use ($orderId) {
            $this->service->confirmPayment($orderId);
            $this->logStockOperation('confirm_payment', "Payment confirmed for order {$orderId}, stock deducted");
            return $this->success('Payment confirmed, stock deducted');
        });
    }

    public function cancelOrder(int $orderId): array
    {
        return $this->handle(function () use ($orderId) {
            $this->service->cancelOrder($orderId);
            $this->logStockOperation('cancel_order', "Order {$orderId} cancelled, reserved stock returned");
            return $this->success('Order cancelled, stock returned');
        });
    }

    public function refundOrder(int $orderId): array
    {
        return $this->handle(function () use ($orderId) {
            $this->service->refundOrder($orderId);
            $this->logStockOperation('refund_order', "Order {$orderId} refunded, stock returned");
            return $this->success('Order refunded, stock returned');
        });
    }

    // WAREHOUSE OPERATIONS
    public function adjustStock(int $productId, int $warehouseId, int $adjustment, ?string $reason = null): array
    {
        return $this->handle(function () use ($productId, $warehouseId, $adjustment, $reason) {
            $updated = $this->service->adjustStock($productId, $warehouseId, $adjustment, $reason);

            $this->logStockOperation(
                'adjust',
                "Stock adjusted: Product {$productId}, Warehouse {$warehouseId}, Change: {$adjustment}, New Qty: {$updated['quantity']}, Reason: {$reason}"
            );
            
            return $this->success('Stock adjusted', $updated);
        });
    }

    public function transferStock(int $productId, int $fromWarehouseId, int $toWarehouseId, int $quantity, ?string $reason = null): array
    {
        return $this->handle(function () use ($productId, $fromWarehouseId, $toWarehouseId, $quantity, $reason) {
            $result = $this->service->transferStock($productId, $fromWarehouseId, $toWarehouseId, $quantity, $reason);

            $this->logStockOperation(
                'transfer',
                "Transferred {$quantity} units of product {$productId} from warehouse {$fromWarehouseId} to {$toWarehouseId}. Reason: {$reason}"
            );

            return $this->success('Stock transferred successfully', $result);
        });
    }

    // FRONTEND HELPERS
    public function getAvailableStock(int $productId): array
    {
        return $this->handle(function () use ($productId) {
            $available = $this->service->getAvailableStock($productId);
            return $this->success('Available stock retrieved', $available);
        });
    }

    public function getStockSummary(int $productId): array
    {
        return $this->handle(function () use ($productId) {
            $summary = $this->service->getStockSummary($productId);
            return $this->success('Stock summary retrieved', $summary);
        });
    }

    // CRUD OPERATIONS
    public function create(array $data): array
    {
        return $this->handle(function () use ($data) {
            $stock = $this->service->create($data);
            
            $this->logStockOperation(
                'create',
                "Created stock entry: Product {$data['product_id']}, Warehouse {$data['warehouse_id']}, Quantity {$data['quantity']}"
            );
            
            return $this->success('Stock created', $stock, 201);
        });
    }

    public function getAll(int $limit = 50, int $offset = 0): array
    {
        return $this->handle(function () use ($limit, $offset) {
            $data = $this->service->getAll($limit, $offset);
            return $this->success('Stocks retrieved', $data);
        });
    }

    public function search(string $query, int $limit = 50, int $offset = 0): array
    {
        return $this->handle(function () use ($query, $limit, $offset) {
            $data = $this->service->search($query, $limit, $offset);
            return $this->success('Stocks found', $data);
        });
    }

    public function getById(int $id): array
    {
        return $this->handle(function () use ($id) {
            $stock = $this->service->getById($id);
            return $this->success('Stock retrieved', $stock);
        });
    }

    /**
     * Get enriched stock data with product and warehouse names
     */
    public function getByIdEnriched(int $id): array
    {
        return $this->handle(function () use ($id) {
            $stock = $this->service->getByIdEnriched($id);
            return $this->success('Stock retrieved', $stock);
        });
    }

    public function getByProductWarehouse(int $productId, int $warehouseId): array
    {
        return $this->handle(function () use ($productId, $warehouseId) {
            $stock = $this->service->getByProductWarehouse($productId, $warehouseId);
            return $this->success('Stock retrieved', $stock);
        });
    }

    public function getByProduct(int $productId): array
    {
        return $this->handle(function () use ($productId) {
            $data = $this->service->getByProduct($productId);
            return $this->success('Product stocks retrieved', $data);
        });
    }

    public function getByWarehouse(int $warehouseId): array
    {
        return $this->handle(function () use ($warehouseId) {
            $data = $this->service->getByWarehouse($warehouseId);
            return $this->success('Warehouse stocks retrieved', $data);
        });
    }

    public function count(): array
    {
        return $this->handle(function () {
            $count = $this->service->count();
            return $this->success('Count retrieved', $count);
        });
    }

    public function update(int $id, array $data): array
    {
        return $this->handle(function () use ($id, $data) {
            $updated = $this->service->update($id, $data);
            
            $this->logStockOperation(
                'update',
                "Updated stock ID {$id}: " . json_encode($data)
            );
            
            return $this->success('Stock updated', $updated);
        });
    }

    public function updateByProductWarehouse(int $productId, int $warehouseId, array $data): array
    {
        return $this->handle(function () use ($productId, $warehouseId, $data) {
            $updated = $this->service->updateByProductWarehouse($productId, $warehouseId, $data);
            
            $this->logStockOperation(
                'update',
                "Updated stock Product {$productId} Warehouse {$warehouseId}: " . json_encode($data)
            );
            
            return $this->success('Stock updated', $updated);
        });
    }

    public function delete(int $id): array
    {
        return $this->handle(function () use ($id) {
            $this->service->delete($id);
            
            $this->logStockOperation(
                'delete',
                "Deleted stock ID {$id}"
            );
            
            return $this->success('Stock deleted');
        });
    }

    public function deleteByProductWarehouse(int $productId, int $warehouseId): array
    {
        return $this->handle(function () use ($productId, $warehouseId) {
            $this->service->deleteByProductWarehouse($productId, $warehouseId);
            
            $this->logStockOperation(
                'delete',
                "Deleted stock Product {$productId} Warehouse {$warehouseId}"
            );
            
            return $this->success('Stock deleted');
        });
    }

    // HELPERS
    private function logStockOperation(string $operation, string $details): void
    {
        error_log(sprintf(
            "[%s] STOCK_OPERATION: %s | %s | User: %s",
            date('Y-m-d H:i:s'),
            strtoupper($operation),
            $details,
            $this->session->getUserId() ?? 'system'
        ));
    }
}
