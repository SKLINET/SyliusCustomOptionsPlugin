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

2. To create order item with customer option please call endpoint PATCH `/api/v2/shop/orders/{tokenValue}/items`
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

3. If you want to edit cart item customerOption, you can call endpoint PATCH `/api/v2/shop/order-items/{id}/update-customer-options`, where {id} is orderItem id
Request body accepts same property.
```JSON
{
  "customerOptions": {
    "CUSTOMER_OPTION_CODE_FIRST": "New value 1",
    "CUSTOMER_OPTION_CODE_SECOND": "New value 2",
    ...  
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
