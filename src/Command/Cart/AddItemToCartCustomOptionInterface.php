<?php

declare(strict_types=1);

namespace Brille24\SyliusCustomerOptionsPlugin\Command\Cart;

interface AddItemToCartCustomOptionInterface
{
    public function setCustomerOptions(array $customerOptions = array()): void;

    public function getCustomerOptions(): ?array;
}
