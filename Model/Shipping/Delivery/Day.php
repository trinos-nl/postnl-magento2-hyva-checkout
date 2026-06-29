<?php

namespace PostNL\HyvaCheckout\Model\Shipping\Delivery;
class Day
{
    public function __construct(
        private readonly array $options,
        private readonly ?string $date = null,
        private readonly ?string $weekDay = null
    ) {
    }

    public function getDate(): ?string
    {
        return $this->date;
    }

    public function getOptions(): array
    {
        return $this->options;
    }

    public function getWeekDay(): ?string
    {
        return $this->weekDay;
    }
}
