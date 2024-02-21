<?php

declare(strict_types=1);

/**
 * @copyright 2023 Akeneo SAS (https://www.akeneo.com)
 * @license https://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

namespace AkeneoTest\Pim\Structure\Integration\AttributeGroup\EventSubscriber;

use Akeneo\Pim\Structure\Component\Exception\AttributeGroupOtherCannotBeRemoved;
use Akeneo\Pim\Structure\Component\Repository\AttributeGroupRepositoryInterface;
use Akeneo\Test\Integration\Configuration;
use Akeneo\Test\Integration\TestCase;
use Akeneo\Tool\Component\StorageUtils\Remover\RemoverInterface;

class CheckAttributeGroupOtherCannotBeRemovedSubscriberIntegration extends TestCase
{
    private AttributeGroupRepositoryInterface $attributeGroupRepository;
    private RemoverInterface $attributeGroupRemover;

    public function setUp(): void
    {
        parent::setUp();
        $this->attributeGroupRepository = $this->get('pim_catalog.repository.attribute_group');
        $this->attributeGroupRemover = $this->get('pim_catalog.remover.attribute_group');
    }

    public function test_it_throws_an_exception_when_the_attribute_group_other_is_deleted(): void
    {
        $this->expectException(AttributeGroupOtherCannotBeRemoved::class);
        $this->removeAttributeGroup('other');
    }

    private function removeAttributeGroup(string $attributeGroupCode): void
    {
        $attributeGroup = $this->attributeGroupRepository->findOneByIdentifier($attributeGroupCode);
        $this->attributeGroupRemover->remove($attributeGroup);
    }

    protected function getConfiguration(): Configuration
    {
        return $this->catalog->useMinimalCatalog();
    }
}
