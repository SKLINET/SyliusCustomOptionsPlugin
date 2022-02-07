<?php

declare(strict_types=1);

namespace Brille24\SyliusCustomerOptionsPlugin\CommandHandler\Cart;

use Brille24\SyliusCustomerOptionsPlugin\Command\Cart\UpdateItemCustomerOptions;
use Brille24\SyliusCustomerOptionsPlugin\Entity\OrderItemInterface;
use Brille24\SyliusCustomerOptionsPlugin\Exceptions\CustomerOptionValidatorException;
use Brille24\SyliusCustomerOptionsPlugin\Services\OrderItemOptionUpdaterInterface;
use Sylius\Component\Core\Repository\OrderItemRepositoryInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;
use Webmozart\Assert\Assert;

final class UpdateItemCustomerOptionsHandler implements MessageHandlerInterface
{

    private RequestStack $requestStack;
    private OrderItemRepositoryInterface $orderItemRepository;
    private OrderItemOptionUpdaterInterface $orderItemOptionUpdater;

    public function __construct(
        RequestStack $requestStack,
        OrderItemRepositoryInterface $orderItemRepository,
        OrderItemOptionUpdaterInterface $orderItemOptionUpdater
    ) {
        $this->requestStack = $requestStack;
        $this->orderItemRepository = $orderItemRepository;
        $this->orderItemOptionUpdater = $orderItemOptionUpdater;
    }

    public function __invoke(UpdateItemCustomerOptions $updateItemCustomerOptions): OrderItemInterface
    {
        /** @var OrderItemInterface|null $orderItem */
        $orderItem = $this->orderItemRepository->find((int)$updateItemCustomerOptions->orderItemId);

        // TODO: maybe useless as api platform if order item with passed id exists?
        Assert::notNull(
            $orderItem,
            sprintf('Order item with id "%s" has not been found', $updateItemCustomerOptions->orderItemId)
        );
        //
        Assert::isInstanceOf($orderItem, OrderItemInterface::class);

        // Check if all required data has been sent
        foreach ($updateItemCustomerOptions->customerOptions as $optionCode => $newValue) {
            Assert::true(is_scalar($newValue), 'Value of customerOption\'s optionCode can be only scalar value.');

            $customerOptions[][$optionCode] = $newValue;
        }

        // Check if passed customer options exists in order item
        $existingCustomerOptionsKeys = array_keys($orderItem->getCustomerOptionConfigurationAsSimpleArray());

        foreach ($customerOptions as $customerOption) {
            foreach ($customerOption as $key => $value) {
                if (!in_array($key, $existingCustomerOptionsKeys)) {
                    throw new CustomerOptionValidatorException(
                        sprintf(
                            'You cannot change customer option with key "%s" as this customer option not exists in passed order item "%s"',
                            $key,
                            $updateItemCustomerOptions->orderItemId
                        )
                    );
                }
            }
        }

        // TODO: add some aditional validation? (if user has sent required customer options, etc)

        if (is_array($customerOptions)) {
            // Update every customer option
            foreach ($customerOptions as $data) {
                $this->orderItemOptionUpdater->updateOrderItemOptions($orderItem, $data);
            }

        }

        return $orderItem;
    }

}
