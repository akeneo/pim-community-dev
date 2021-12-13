<?php

declare(strict_types=1);

namespace AkeneoTestEnterprise\Pim\Permission\FixturesLoader;

use Akeneo\Pim\Structure\Component\Model\AttributeGroupInterface;
use Akeneo\Pim\Structure\Component\Updater\AttributeGroupUpdater;
use Akeneo\Tool\Component\StorageUtils\Factory\SimpleFactoryInterface;
use Akeneo\Tool\Component\StorageUtils\Saver\SaverInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class AttributeGroupFixturesLoader
{
    private SimpleFactoryInterface $attributeGroupFactory;
    private AttributeGroupUpdater $attributeGroupUpdater;
    private SaverInterface $attributeGroupSaver;
    private ValidatorInterface $validator;

    public function __construct(
        SimpleFactoryInterface $attributeGroupFactory,
        AttributeGroupUpdater $attributeGroupUpdater,
        SaverInterface $attributeGroupSaver,
        ValidatorInterface $validator
    ) {
        $this->attributeGroupFactory = $attributeGroupFactory;
        $this->attributeGroupUpdater = $attributeGroupUpdater;
        $this->attributeGroupSaver = $attributeGroupSaver;
        $this->validator = $validator;
    }

    public function createAttributeGroup(array $data = []): AttributeGroupInterface
    {
        /** @var AttributeGroupInterface $attributeGroup */
        $attributeGroup = $this->attributeGroupFactory->create();
        $this->attributeGroupUpdater->update($attributeGroup, $data);
        $this->validator->validate($attributeGroup);
        $this->attributeGroupSaver->save($attributeGroup);

        return $attributeGroup;
    }
}
