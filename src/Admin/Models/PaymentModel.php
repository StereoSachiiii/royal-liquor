<?php
declare(strict_types=1);

namespace App\Admin\Models;

class PaymentModel
{
    public function __construct(
        private ?int $id = null,
        private ?int $order_id = null,
        private int $amount_cents = 0,
        private ?string $currency = 'LKR',
        private ?string $gateway = null,
        private ?string $gateway_order_id = null,
        private ?string $transaction_id = null,
        private ?string $status = 'pending',
        private ?array $payload = null,
        private ?string $created_at = null
    ) {}

    // Getters
    public function getId(): ?int { return $this->id; }
    public function getOrderId(): ?int { return $this->order_id; }
    public function getAmountCents(): int { return $this->amount_cents; }
    public function getCurrency(): ?string { return $this->currency; }
    public function getGateway(): ?string { return $this->gateway; }
    public function getGatewayOrderId(): ?string { return $this->gateway_order_id; }
    public function getTransactionId(): ?string { return $this->transaction_id; }
    public function getStatus(): ?string { return $this->status; }
    public function getPayload(): ?array { return $this->payload; }
    public function getCreatedAt(): ?string { return $this->created_at; }

    public function toArray(): array
    {
        return [
            'id'                => $this->id,
            'order_id'          => $this->order_id,
            'amount_cents'      => $this->amount_cents,
            'currency'          => $this->currency,
            'gateway'           => $this->gateway,
            'gateway_order_id'  => $this->gateway_order_id,
            'transaction_id'    => $this->transaction_id,
            'status'            => $this->status,
            'payload'           => $this->payload,
            'created_at'        => $this->created_at,
        ];
    }
}
