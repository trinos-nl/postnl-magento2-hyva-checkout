<?php

namespace PostNL\HyvaCheckout\Model\Shipping\Delivery;

use function __;
use function strtolower;

class Timeframe
{
    public function __construct(
        private readonly string $value,
        private readonly string $label,
        private readonly ?string $rightLabel = null,
        private readonly ?string $fee = null
    ) {
    }

    public function getValue(): string
    {
        return $this->value;
    }

    public function getLabel(): string
    {
        return $this->label;
    }

    public function getRightLabel(): string
    {
        $label = strtolower((string) $this->rightLabel);

        return match ($label) {
            'daytime' => __('Daytime')->render(),
            'evening' => __('Evening')->render(),
            'noon' => __('Morning')->render(),
            default => '',
        };
    }

    public function getFee(): ?string
    {
        return $this->fee;
    }
}
