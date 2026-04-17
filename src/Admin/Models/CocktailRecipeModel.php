<?php
declare(strict_types=1);

namespace App\Admin\Models;

class CocktailRecipeModel
{
    public function __construct(
        public int $id,
        public string $name,
        public ?string $description,
        public string $instructions,
        public ?string $image_url,
        // Required fields first
        public string $created_at,

        // Optional fields after required ones
        public string $difficulty = 'easy',
        public ?int $preparation_time = null,
        public int $serves = 1,
        public bool $is_active = true,
        public ?string $updated_at = null,
        public ?string $deleted_at = null
    ) {}

    public function toArray(): array
    {
        return [
            'id'                => $this->id,
            'name'              => $this->name,
            'description'       => $this->description,
            'instructions'      => $this->instructions,
            'image_url'         => $this->image_url,
            'difficulty'        => $this->difficulty,
            'preparation_time'  => $this->preparation_time,
            'serves'            => $this->serves,
            'is_active'         => $this->is_active,
            'created_at'        => $this->created_at,
            'updated_at'        => $this->updated_at,
            'deleted_at'        => $this->deleted_at,
        ];
    }
}
