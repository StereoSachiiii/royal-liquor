<?php
declare(strict_types=1);

namespace App\Admin\Models;

class FlavorProfileModel
{
    public function __construct(
        private int $product_id,
        private int $sweetness = 5,
        private int $bitterness = 5,
        private int $strength = 5,
        private int $smokiness = 5,
        private int $fruitiness = 5,
        private int $spiciness = 5,
        private array $tags = []
    ) {}

    // Getters
    public function getProductId(): int { return $this->product_id; }
    public function getSweetness(): int { return $this->sweetness; }
    public function getBitterness(): int { return $this->bitterness; }
    public function getStrength(): int { return $this->strength; }
    public function getSmokiness(): int { return $this->smokiness; }
    public function getFruitiness(): int { return $this->fruitiness; }
    public function getSpiciness(): int { return $this->spiciness; }
    public function getTags(): array { return $this->tags; }

    public function toArray(): array
    {
        return [
            'product_id' => $this->product_id,
            'sweetness'  => $this->sweetness,
            'bitterness' => $this->bitterness,
            'strength'   => $this->strength,
            'smokiness'  => $this->smokiness,
            'fruitiness' => $this->fruitiness,
            'spiciness'  => $this->spiciness,
            'tags'       => $this->tags
        ];
    }
}
