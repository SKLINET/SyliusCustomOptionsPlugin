<?php

declare(strict_types=1);

namespace Brille24\SyliusCustomerOptionsPlugin\CommandHandler\Cart;

use Brille24\SyliusCustomerOptionsPlugin\Command\Cart\AddItemToCartCustomOptionInterface;
use Brille24\SyliusCustomerOptionsPlugin\Entity\OrderItemInterface;
use Brille24\SyliusCustomerOptionsPlugin\Exceptions\CustomerOptionValidatorException;
use Brille24\SyliusCustomerOptionsPlugin\Services\OrderItemOptionUpdaterInterface;
use Webmozart\Assert\Assert;

trait AddItemToCartCustomOptionHandlerTrait
{
    public function updateOrderItemOption(
        OrderItemInterface $orderItem,
        AddItemToCartCustomOptionInterface $addItemToCartCustomOption,
        OrderItemOptionUpdaterInterface $orderItemOptionUpdater
    ): void {
        $customerOptions = null;

        if (!is_array($addItemToCartCustomOption->getCustomerOptions())) {
            $orderItemOptionUpdater->removeOrderItemOptions($orderItem);

            return;
        }

        // Check if all required data has been sent
        foreach ($addItemToCartCustomOption->getCustomerOptions() as $optionCode => $newValue) {
            Assert::true(is_scalar($newValue), 'Value of customerOption\'s optionCode can be only scalar value.');

            $customerOptions[][$optionCode] = $newValue;
        }

        // TODO: add some aditional validation? (if user has sent required customer options, etc)

        // If no customer option has been selected remove all order item customer options
        if(!is_array($customerOptions)) {
            $orderItemOptionUpdater->removeOrderItemOptions($orderItem);

            return;
        }

        // Update every customer option
        foreach ($customerOptions as $data) {
            $orderItemOptionUpdater->updateOrderItemOptions($orderItem, $data);
        }
    }
}
