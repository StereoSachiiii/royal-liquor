<?php
declare(strict_types=1);

namespace App\Admin\Repositories;

use PDO;
use App\Admin\Models\PaymentModel;
use App\Admin\Exceptions\NotFoundException;
use App\Admin\Exceptions\DatabaseException;

class PaymentRepository extends BaseRepository
{
    public function getAll(int $limit = 50, int $offset = 0): array
    {
        $sql = "SELECT * FROM payments ORDER BY created_at DESC LIMIT :limit OFFSET :offset";
        $rows = $this->fetchAll($sql, [
            ':limit' => $limit,
            ':offset' => $offset
        ]);
        return $this->mapToModels($rows);
    }

    public function search(string $query, int $limit = 50, int $offset = 0): array
    {
        $sql = "SELECT p.*, o.order_number 
                FROM payments p
                LEFT JOIN orders o ON p.order_id = o.id
                WHERE o.order_number ILIKE :query 
                   OR p.gateway ILIKE :query 
                   OR p.transaction_id ILIKE :query
                ORDER BY p.created_at DESC 
                LIMIT :limit OFFSET :offset";
        $rows = $this->fetchAll($sql, [
            ':query' => '%' . $query . '%',
            ':limit' => $limit,
            ':offset' => $offset
        ]);
        return $this->mapToModels($rows);
    }

    public function getById(int $id): ?PaymentModel
    {
        $row = $this->fetchOne("SELECT * FROM payments WHERE id = :id", [':id' => $id]);
        return $row ? $this->mapToModel($row) : null;
    }

    public function getByOrder(int $orderId): array
    {
        $rows = $this->fetchAll("SELECT * FROM payments WHERE order_id = :order_id", [':order_id' => $orderId]);
        return $this->mapToModels($rows);
    }

    public function count(): int
    {
        return (int)$this->fetchColumn("SELECT COUNT(*) FROM payments");
    }

    public function hardDelete(int $id): bool
    {
        $stmt = $this->executeStatement("DELETE FROM payments WHERE id = :id", [':id' => $id]);
        return $stmt->rowCount() > 0;
    }

    public function create(array $data): PaymentModel
    {
        $sql = "INSERT INTO payments (order_id, amount_cents, currency, gateway, 
                  gateway_order_id, transaction_id, status, payload) 
                 VALUES (:order_id, :amount_cents, :currency, :gateway, 
                  :gateway_order_id, :transaction_id, :status, :payload) 
                 RETURNING *";
        
        $stmt = $this->executeStatement($sql, [
            ':order_id' => $data['order_id'],
            ':amount_cents' => $data['amount_cents'],
            ':currency' => $data['currency'] ?? 'LKR',
            ':gateway' => $data['gateway'],
            ':gateway_order_id' => $data['gateway_order_id'] ?? null,
            ':transaction_id' => $data['transaction_id'] ?? null,
            ':status' => $data['status'] ?? 'pending',
            ':payload' => isset($data['payload']) ? json_encode($data['payload']) : null
        ]);
        
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$row) {
            throw new DatabaseException('Failed to create payment');
        }
        return $this->mapToModel($row);
    }

    public function update(int $id, array $data): ?PaymentModel
    {
        $sets = [];
        $params = [':id' => $id];

        foreach (['status', 'transaction_id', 'payload'] as $col) {
            if (isset($data[$col])) {
                $sets[] = "$col = :$col";
                $params[":$col"] = $col === 'payload' ? json_encode($data[$col]) : $data[$col];
            }
        }

        if (empty($sets)) {
            return null;
        }

        $sql = "UPDATE payments SET " . implode(', ', $sets) . " 
                WHERE id = :id RETURNING *";
        
        $stmt = $this->executeStatement($sql, $params);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ? $this->mapToModel($row) : null;
    }

    public function delete(int $id): bool
    {
        $stmt = $this->executeStatement("DELETE FROM payments WHERE id = :id", [':id' => $id]);
        return $stmt->rowCount() > 0;
    }

    protected function mapToModel(array $row): PaymentModel
    {
        return new PaymentModel(
            id: (int)$row['id'],
            order_id: (int)$row['order_id'],
            amount_cents: (int)$row['amount_cents'],
            currency: $row['currency'],
            gateway: $row['gateway'],
            gateway_order_id: $row['gateway_order_id'],
            transaction_id: $row['transaction_id'],
            status: $row['status'],
            payload: $row['payload'] ? json_decode($row['payload'], true) : null,
            created_at: $row['created_at']
        );
    }

    protected function mapToModels(array $rows): array
    {
        return array_map(fn($row) => $this->mapToModel($row), $rows);
    }
}
