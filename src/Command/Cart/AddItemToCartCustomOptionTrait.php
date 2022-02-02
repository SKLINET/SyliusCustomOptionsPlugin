<?php

declare(strict_types=1);

namespace Brille24\SyliusCustomerOptionsPlugin\Command\Cart;

trait AddItemToCartCustomOptionTrait
{
    /**
     * @var array<string, mixed> $customerOptions
     * Associative array of key value pairs for the new array.
     * The key is the custom option code and the value is the new value.
     */
    public $customerOptions;

	public function setCustomerOptions(?array $customerOptions = null): void
	{
		$this->customerOptions = $customerOptions;
	}

	public function getCustomerOptions(): ?array
	{
		return $this->customerOptions;
	}
}
