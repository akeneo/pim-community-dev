<?php

declare(strict_types=1);

namespace AkeneoTest\Pim\Structure\Integration\Attribute\Query;

use Akeneo\Pim\Structure\Component\Exception\CannotRemoveAttributeException;
use Akeneo\Test\Integration\Configuration;
use Akeneo\Test\Integration\TestCase;

class CheckAttributeIsNotIdentifierOnDeletionSubscriberIntegration extends TestCase
{
    public function test_it_throws_an_exception_when_the_attribute_is_an_identifier(): void
    {
        $attribute = $this->get('pim_catalog.repository.attribute')->findOneByIdentifier('sku');
        $this->expectException(CannotRemoveAttributeException::class);
        $this->get('pim_catalog.remover.attribute')->remove($attribute);
    }

    protected function getConfiguration(): Configuration
    {
        return $this->catalog->useMinimalCatalog();
    }
}
