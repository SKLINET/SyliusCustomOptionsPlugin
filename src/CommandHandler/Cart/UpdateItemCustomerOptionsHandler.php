<?php

declare(strict_types=1);

namespace Brille24\SyliusCustomerOptionsPlugin\CommandHandler\Cart;

use Brille24\SyliusCustomerOptionsPlugin\Command\Cart\UpdateItemCustomerOptions;
use Brille24\SyliusCustomerOptionsPlugin\Entity\OrderItemInterface;
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
        // TODO: replace request
        $request = $this->requestStack->getCurrentRequest();
        $itemId = $request->get('id');

        Assert::notNull($itemId, 'Item id has not been found in current request');

        // TODO: if request will be replaced this line is not necessary
        $updateItemCustomerOptions->id = $itemId;

        ////////////////////////////////////////////////////////////////////////////

        /** @var OrderItemInterface|null $orderItem */
        $orderItem = $this->orderItemRepository->find((int)$updateItemCustomerOptions->id);

        // TODO: maybe useless as api platform if order item with passed id exists?
        Assert::notNull($orderItem, sprintf('Order item with id "%s" has not been found', $itemId));
        //
        Assert::isInstanceOf($orderItem, OrderItemInterface::class);

        // Check if all required data has been sent
        foreach ($updateItemCustomerOptions->customerOptions as $optionCode => $newValue) {
            Assert::true(is_scalar($newValue), 'Value of customerOption\'s optionCode can be only scalar value.');

            $customerOptions[][$optionCode] = $newValue;
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
