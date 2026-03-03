<?php

declare(strict_types=1);

/**
 * @copyright 2023 Akeneo SAS (https://www.akeneo.com)
 * @license https://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

namespace AkeneoTest\Pim\Structure\Integration\AttributeGroup\EventSubscriber;

use Akeneo\Pim\Structure\Component\Exception\AttributeGroupOtherCannotBeRemoved;
use Akeneo\Pim\Structure\Component\Exception\AttributeGroupWithAttributeCannotBeRemoved;
use Akeneo\Pim\Structure\Component\Repository\AttributeGroupRepositoryInterface;
use Akeneo\Test\Integration\Configuration;
use Akeneo\Test\Integration\TestCase;
use Akeneo\Tool\Component\StorageUtils\Remover\RemoverInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Webmozart\Assert\Assert;

class CheckAttributeGroupWithAttributeCannotBeRemovedSubscriberIntegration extends TestCase
{
    private AttributeGroupRepositoryInterface $attributeGroupRepository;
    private RemoverInterface $attributeGroupRemover;
    private ValidatorInterface $validator;

    public function setUp(): void
    {
        parent::setUp();
        $this->validator = $this->get('validator');
        $this->attributeGroupRepository = $this->get('pim_catalog.repository.attribute_group');
        $this->attributeGroupRemover = $this->get('pim_catalog.remover.attribute_group');
    }

    public function test_it_throws_an_exception_when_the_attribute_group_have_attribute(): void
    {
        $this->givenAttributeGroup('an_attribute_group_with_attribute');
        $this->givenAttribute('an_attribute', 'an_attribute_group_with_attribute');

        $this->expectException(AttributeGroupWithAttributeCannotBeRemoved::class);
        $this->removeAttributeGroup('an_attribute_group_with_attribute');
    }

    private function givenAttributeGroup(string $attributeGroupCode): void
    {
        $attributeGroup = $this->get('pim_catalog.factory.attribute_group')->create();
        $this->get('pim_catalog.updater.attribute_group')->update($attributeGroup, [
            'code' => $attributeGroupCode
        ]);

        $constraintViolations = $this->validator->validate($attributeGroup);
        Assert::count($constraintViolations, 0);

        $this->get('pim_catalog.saver.attribute_group')->save($attributeGroup);
    }

    private function givenAttribute(string $attributeCode, string $attributeGroupCode): void
    {
        $attribute = $this->get('pim_catalog.factory.attribute')->create();
        $this->get('pim_catalog.updater.attribute')->update($attribute, [
            'code' => $attributeCode,
            'type' => 'pim_catalog_text',
            'group' => $attributeGroupCode
        ]);

        $constraintViolations = $this->validator->validate($attribute);
        Assert::count($constraintViolations, 0);

        $this->get('pim_catalog.saver.attribute')->save($attribute);
    }

    private function removeAttributeGroup(string $attributeGroupCode): void
    {
        $this->get('pim_connector.doctrine.cache_clearer')->clear();

        $attributeGroup = $this->attributeGroupRepository->findOneByIdentifier($attributeGroupCode);
        $this->attributeGroupRemover->remove($attributeGroup);
    }

    protected function getConfiguration(): Configuration
    {
        return $this->catalog->useMinimalCatalog();
    }
}
