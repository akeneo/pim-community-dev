<?php

declare(strict_types=1);

namespace Specification\Akeneo\Pim\Automation\FranklinInsights\Domain\Subscription\Model\Read;

use Akeneo\Pim\Automation\FranklinInsights\Domain\Common\ValueObject\ProductId;
use Akeneo\Pim\Automation\FranklinInsights\Domain\Subscription\Model\Read\ProductIdentifierValues;
use Akeneo\Pim\Automation\FranklinInsights\Domain\Subscription\Model\Read\ProductIdentifierValuesCollection;
use PhpSpec\ObjectBehavior;

class ProductIdentifierValuesCollectionSpec extends ObjectBehavior
{
    public function it_is_a_product_identifier_values_collection(): void
    {
        $this->shouldHaveType(ProductIdentifierValuesCollection::class);
    }

    public function it_stores_product_identifier_values_read_models(): void
    {
        $this->count()->shouldReturn(0);
        $this->add(new ProductIdentifierValues(new ProductId(42), []));

        $this->count()->shouldReturn(1);
    }

    public function it_retrieves_a_product_identifier_values_with_its_product_id(): void
    {
        $identifierValues = new ProductIdentifierValues(new ProductId(42), []);
        $this->add($identifierValues);

        $this->get(new ProductId(42))->shouldReturn($identifierValues);
        $this->get(new ProductId(56))->shouldReturn(null);
    }
}
