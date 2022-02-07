<?php

declare(strict_types=1);

namespace Brille24\SyliusCustomerOptionsPlugin\Command\Cart;

use Sylius\Bundle\ApiBundle\Command\OrderTokenValueAwareInterface;
use Sylius\Bundle\ApiBundle\Command\SubresourceIdAwareInterface;

final class UpdateItemCustomerOptions  implements OrderTokenValueAwareInterface, SubresourceIdAwareInterface
{
    /**
     * @var string|null Order item id
     */
    public $orderItemId;

    /**
     * @var string|null Order token value
     */
    public $orderTokenValue;

    /**
     * @var array<>
     */
    public $customerOptions;

    public function getOrderTokenValue(): ?string
    {
        return $this->orderTokenValue;
    }

    public function setOrderTokenValue(?string $orderTokenValue): void
    {
        $this->orderTokenValue = $orderTokenValue;
    }

    public function getSubresourceId(): ?string
    {
        return $this->orderItemId;
    }

    public function setSubresourceId(?string $subresourceId): void
    {
        $this->orderItemId = $subresourceId;
    }

    public function getSubresourceIdAttributeKey(): string
    {
        return 'orderItemId';
    }
}
