# Customer Options
<p align="center"><img src="https://sylius.com/assets/badge-approved-by-sylius.png" width="100px"></p>

With this plugin the customer can add additional info to the product like so:
![Price import forms](docs/images/customeroption_frontend.png "The customer can upload a file")
![Price import forms](docs/images/customeroption_frontend_cart.png "And it will be displayed in the cart")

## Installation

* Run `composer require brille24/sylius-customer-options-plugin`.

* Register the Plugin in your `config/bundles.php`:

```php
return [
    //...
    Brille24\SyliusCustomerOptionsPlugin\Brille24SyliusCustomerOptionsPlugin::class => ['all' => true],
];
```
* Add the `config.yml` to your local `config/packages/_sylius.yaml`:
```yaml
imports:
    ...
    - { resource: "@Brille24SyliusCustomerOptionsPlugin/Resources/config/app/config.yml" }
```

* Add the `routing.yml` to your local `config/routes.yaml`:
```yaml
brille24_customer_options:
    resource: "@Brille24SyliusCustomerOptionsPlugin/Resources/config/app/routing.yml"
```

* Copy the template overrides from the plugin directory
```
From: [shop_dir]/vendor/brille24/sylius-customer-options-plugin/test/Application/templates
To: [shop_dir]/templates
```

In order to use the customer options, you need to override the product, product variant and order item.
```php
use Brille24\SyliusCustomerOptionsPlugin\Entity\ProductInterface;
use Brille24\SyliusCustomerOptionsPlugin\Traits\ProductCustomerOptionCapableTrait;
use Sylius\Component\Core\Model\Product as BaseProduct;

class Product extends BaseProduct implements ProductInterface {
    use ProductCustomerOptionCapableTrait {
        __construct as protected customerOptionCapableConstructor;
    }
    
     public function __construct()
    {
        parent::__construct();

        $this->customerOptionCapableConstructor();
    }
    // ...
}
```

```php
use Brille24\SyliusCustomerOptionsPlugin\Traits\ProductVariantCustomerOptionCapableTraitInterface;
use Brille24\SyliusCustomerOptionsPlugin\Traits\ProductVariantCustomerOptionCapableTrait;
use Sylius\Component\Core\Model\ProductVariant as BaseProductVariant;

class ProductVariant extends BaseProductVariant implements ProductVariantCustomerOptionCapableTraitInterface
{
    use ProductVariantCustomerOptionCapableTrait;
    
    ...
}
```

```php
use Brille24\SyliusCustomerOptionsPlugin\Entity\OrderItemInterface;
use Brille24\SyliusCustomerOptionsPlugin\Traits\OrderItemCustomerOptionCapableTrait;
use Sylius\Component\Core\Model\OrderItem as BaseOrderItem;

class OrderItem extends BaseOrderItem implements OrderItemInterface
{
    use OrderItemCustomerOptionCapableTrait {
        __construct as protected customerOptionCapableConstructor;
    }

    public function __construct()
    {
        parent::__construct();

        $this->customerOptionCapableConstructor();
    }
    // ...
}
```

# API V2 support

1. Plugin defines new resources (CustomerOption). Please open API documentation for more details.

2. Create file Order.xml in 'config/api_platform' folder

```xml

<resources xmlns="https://api-platform.com/schema/metadata"
           xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
           xsi:schemaLocation="https://api-platform.com/schema/metadata https://api-platform.com/schema/metadata/metadata-2.0.xsd"
>
   ...
    <itemOperation name="shop_add_item">
        ...
        <!-- Edit - (customer option plugin) -->
        <attribute name="input">App\Command\Cart\AddItemToCart</attribute>
        <!-- Edit - (customer option plugin) -->
        ...
    </itemOperation>
    <!-- Customer option plugin -->
    <itemOperation name="shop_customer_options_change_quantity">
        <attribute name="method">PATCH</attribute>
        <attribute name="path">/shop/orders/{tokenValue}/items/{orderItemId}/update-customer-options</attribute>
        <attribute name="messenger">input</attribute>
        <attribute name="input">Brille24\SyliusCustomerOptionsPlugin\Command\Cart\UpdateItemCustomerOptions</attribute>
        <attribute name="normalization_context">
            <attribute name="groups">shop:cart:read</attribute>
        </attribute>
        <attribute name="denormalization_context">
            <attribute name="groups">shop:cart:add_item</attribute>
        </attribute>
        <attribute name="openapi_context">
            <attribute name="summary">Edit customer options for particular cart item</attribute>
            <attribute name="parameters">
                <attribute>
                    <attribute name="name">tokenValue</attribute>
                    <attribute name="in">path</attribute>
                    <attribute name="required">true</attribute>
                    <attribute name="schema">
                        <attribute name="type">string</attribute>
                    </attribute>
                </attribute>
                <attribute>
                    <attribute name="name">orderItemId</attribute>
                    <attribute name="in">path</attribute>
                    <attribute name="required">true</attribute>
                    <attribute name="schema">
                        <attribute name="type">string</attribute>
                    </attribute>
                </attribute>
            </attribute>
        </attribute>
    </itemOperation>
    <!-- Customer option plugin -->
    ...
</resource>
```

3. Create file ProductVariant.xml in 'config/api_platform' folder

```xml

<?xml version="1.0" ?>

<resources xmlns="https://api-platform.com/schema/metadata"
           xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
           xsi:schemaLocation="https://api-platform.com/schema/metadata https://api-platform.com/schema/metadata/metadata-2.0.xsd"
>
    <resource class="%sylius.model.product_variant.class%" shortName="ProductVariant">
        ...
        <!-- Customer option plugin -->
        <property name="customerOptions" readable="true" />
        <!-- Customer option plugin -->
        ...
    </resource>
</resources>

```

4. To create order item with customer option please call endpoint PATCH `/api/v2/shop/orders/{tokenValue}/items`
The endpoint request body accepts besides productVariant and quantity property `customerOptions`, that contains array of selected/filled customer options
```JSON
{
  "productVariant": "PRODUCT_VARIANT_IRI",
  "quantity": 1,
  "customerOptions": {
    "CUSTOMER_OPTION_CODE_FIRST": "Value 1",
    "CUSTOMER_OPTION_CODE_SECOND": "Value 2",
    ...  
  }
}
```

5. If you want to edit cart item customerOption, you can call endpoint PATCH `/api/v2/shop/orders/{tokenValue}/items/{orderItemId}/update-customer-options`
Request body accepts same property as upper one.
```JSON
{
  "customerOptions": {
    "CUSTOMER_OPTION_CODE_FIRST": "New value 1",
    "CUSTOMER_OPTION_CODE_SECOND": "New value 2",
    ...  
  }
}
```

6. Create file AddItemToCart.php in src/Command/Cart folder
```php
<?php

/*
 * This file is part of the Sylius package.
 *
 * (c) Paweł Jędrzejewski
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace App\Command\Cart;

use Brille24\SyliusCustomerOptionsPlugin\Command\Cart\AddItemToCartCustomOptionInterface;
use Brille24\SyliusCustomerOptionsPlugin\Command\Cart\AddItemToCartCustomOptionTrait;
use Sylius\Bundle\ApiBundle\Command\OrderTokenValueAwareInterface;

class AddItemToCart implements OrderTokenValueAwareInterface,
    // Customer option plugin
    AddItemToCartCustomOptionInterface
    // Customer option plugin
{
    // Customer option plugin
    use AddItemToCartCustomOptionTrait;
    // Customer option plugin

    /**
     * @var string|null
     */
    public $orderTokenValue;

    /**
     * @psalm-immutable
     * @var string
     */
    public $productVariantCode;

    /**
     * @psalm-immutable
     * @var int
     */
    public $quantity;

    public function __construct(
        string $productVariantCode,
        int $quantity
    )
    {
        $this->productVariantCode = $productVariantCode;
        $this->quantity = $quantity;
    }

    public static function createFromData(string $tokenValue, string $productVariantCode, int $quantity): self
    {
        $command = new self($productVariantCode, $quantity);

        $command->orderTokenValue = $tokenValue;

        return $command;
    }

    public function getOrderTokenValue(): ?string
    {
        return $this->orderTokenValue;
    }

    public function setOrderTokenValue(?string $orderTokenValue): void
    {
        $this->orderTokenValue = $orderTokenValue;
    }

}

```

6. Create file AddItemToCartHandler.php in src/CommandHandler/Cart folder
```php
<?php

/*
 * This file is part of the Sylius package.
 *
 * (c) Paweł Jędrzejewski
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace App\CommandHandler\Cart;

use ApiPlatform\Core\Api\IriConverterInterface;
use Sylius\Component\Core\Factory\CartItemFactoryInterface;
use Sylius\Component\Core\Model\OrderInterface;
use Sylius\Component\Core\Model\OrderItemInterface;
use Sylius\Component\Core\Model\ProductVariantInterface;
use Sylius\Component\Core\Repository\OrderRepositoryInterface;
use Sylius\Component\Core\Repository\ProductVariantRepositoryInterface;
use Sylius\Component\Order\Modifier\OrderItemQuantityModifierInterface;
use Sylius\Component\Order\Modifier\OrderModifierInterface;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;
use Webmozart\Assert\Assert;
use App\Command\Cart\AddItemToCart;
use Brille24\SyliusCustomerOptionsPlugin\CommandHandler\Cart\AddItemToCartCustomOptionHandlerInterface;
use Brille24\SyliusCustomerOptionsPlugin\CommandHandler\Cart\AddItemToCartCustomOptionHandlerTrait;
use Brille24\SyliusCustomerOptionsPlugin\Services\OrderItemOptionUpdaterInterface;

final class AddItemToCartHandler implements MessageHandlerInterface,
    // Customer option plugin
    AddItemToCartCustomOptionHandlerInterface
    // Customer option plugin
{
    // Customer option plugin
    use AddItemToCartCustomOptionHandlerTrait;
    // Customer option plugin

    private OrderRepositoryInterface $orderRepository;

    private ProductVariantRepositoryInterface $productVariantRepository;

    private OrderModifierInterface $orderModifier;

    private CartItemFactoryInterface $cartItemFactory;

    private OrderItemQuantityModifierInterface $orderItemQuantityModifier;

    // Customer option plugin
    private OrderItemOptionUpdaterInterface $orderItemOptionUpdater;
    // Customer option plugin
    private IriConverterInterface $iriConverter;

    public function __construct(
        OrderRepositoryInterface $orderRepository,
        ProductVariantRepositoryInterface $productVariantRepository,
        OrderModifierInterface $orderModifier,
        CartItemFactoryInterface $cartItemFactory,
        OrderItemQuantityModifierInterface $orderItemQuantityModifier,
        IriConverterInterface $iriConverter,
        // CustomerOptionPlugin
        OrderItemOptionUpdaterInterface $orderItemOptionUpdater
        // CustomerOptionPlugin
    ) {
        $this->orderRepository = $orderRepository;
        $this->productVariantRepository = $productVariantRepository;
        $this->orderModifier = $orderModifier;
        $this->cartItemFactory = $cartItemFactory;
        $this->orderItemQuantityModifier = $orderItemQuantityModifier;
        $this->iriConverter = $iriConverter;
        // CustomerOptionPlugin
        $this->orderItemOptionUpdater = $orderItemOptionUpdater;
        // CustomerOptionPlugin
    }

    public function __invoke(AddItemToCart $addItemToCart): OrderInterface
    {
        // Start - Edit
        /** @var ProductVariantInterface|null $productVariant */
        $productVariant = $this->iriConverter->getItemFromIri($addItemToCart->productVariantCode);

        Assert::notNull($productVariant, 'ProductVariant cannot be null in AddItemToCartHandler');
        Assert::isInstanceOf(
            $productVariant,
            ProductVariantInterface::class,
            sprintf('Product variant with "%s" code does not exist.', $addItemToCart->productVariantCode)
        );
        // Start - End

        /** @var OrderInterface $cart */
        $cart = $this->orderRepository->findCartByTokenValue($addItemToCart->orderTokenValue);

        Assert::notNull($cart, 'Cart cannot be null in AddItemToCartHandler');

        /** @var OrderItemInterface $cartItem */
        $cartItem = $this->cartItemFactory->createNew();
        $cartItem->setVariant($productVariant);

        $this->orderItemQuantityModifier->modify($cartItem, $addItemToCart->quantity);
        $this->orderModifier->addToOrder($cart, $cartItem);

        // CustomerOptionPlugin
        $this->updateOrderItemOption($cartItem, $addItemToCart, $this->orderItemOptionUpdater);
        // CustomerOptionPlugin

        return $cart;
    }
}


```




* If you also want default data you need to copy over the `brille24_sylius_customer_options_plugin_fixtures.yaml` file from the package directory and run
```bash
bin/console sylius:fixtures:load
```

* Finally, update the database and update the translations:
```bash
bin/console doctrine:migrations:migrate
bin/console translation:update
```

## Things to consider
* Saving files as customer defined values as the values are currently stored as a string in the database

## Developing
When developing it is recommended to use git hooks for this just copy the `docs/pre-commit` to `.git/hooks/pre-commit` and make it executable. Then you will check your codestyle before committing.

## Usage
Documentation on how to use the plugin can be found [here](docs/usage.md).
