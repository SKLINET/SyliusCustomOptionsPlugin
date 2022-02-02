<?php

declare(strict_types=1);

namespace Brille24\SyliusCustomerOptionsPlugin\CommandHandler\Cart;

use Brille24\SyliusCustomerOptionsPlugin\Command\Cart\AddItemToCartCustomOptionInterface;
use Brille24\SyliusCustomerOptionsPlugin\Entity\OrderItemInterface;
use Brille24\SyliusCustomerOptionsPlugin\Services\OrderItemOptionUpdaterInterface;

interface AddItemToCartCustomOptionHandlerInterface
{
    public function updateOrderItemOption(
        OrderItemInterface $orderItem,
        AddItemToCartCustomOptionInterface $addItemToCartCustomOption,
        OrderItemOptionUpdaterInterface $orderItemOptionUpdate
    ): void;
}
