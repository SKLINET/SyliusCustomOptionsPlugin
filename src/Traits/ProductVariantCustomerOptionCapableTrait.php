<?php

declare(strict_types=1);

namespace Brille24\SyliusCustomerOptionsPlugin\Traits;

use Brille24\SyliusCustomerOptionsPlugin\Entity\CustomerOptions\CustomerOptionInterface;

trait ProductVariantCustomerOptionCapableTrait
{
    /**
     * @var array|CustomerOptionInterface[]
     */
    protected $customerOptions;

    public function getCustomerOptions(): array
    {
        /** @var ProductCustomerOptionCapableTraitInterface $product */
        $product = $this->getProduct();

        return $product->getCustomerOptions();
    }
}
