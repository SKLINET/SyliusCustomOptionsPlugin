<?php

declare(strict_types=1);

namespace Brille24\SyliusCustomerOptionsPlugin\Command\Cart;

final class UpdateItemCustomerOptions
{
    /**
     * Order Item id
     *
     * @var int
     */
    public $id;

    /**
     * @var array<>
     */
    public $customerOptions;
}
