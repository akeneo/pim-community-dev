<?php

declare(strict_types=1);

namespace Akeneo\Test\Acceptance\Attribute;

use Akeneo\Test\Acceptance\AttributeGroup\InMemoryAttributeGroupRepository;
use Akeneo\Test\Acceptance\AttributeOption\InMemoryAttributeOptionRepository;
use Akeneo\Test\Acceptance\Common\ListOfCodes;
use Akeneo\Test\Common\Builder\EntityBuilder;
use Behat\Behat\Context\Context;
use Pim\Component\Catalog\AttributeTypes;

/**
 * @author    Damien Carcel <damien.carcel@akeneo.com>
 * @author    Julian Prud'homme <julian.prudhomme@akeneo.com>
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class AttributeContext implements Context
{
    public const DEFAULT_ATTRIBUTE_GROUP = 'default_attribute_group';

    /** @var InMemoryAttributeRepository */
    private $attributeRepository;

    /** @var InMemoryAttributeGroupRepository */
    private $attributeGroupRepository;

    /** @var InMemoryAttributeOptionRepository */
    private $attributeOptionRepository;

    /** @var EntityBuilder */
    private $attributeBuilder;

    /** @var EntityBuilder */
    private $attributeGroupBuilder;

    /** @var EntityBuilder */
    private $attributeOptionBuilder;

    /**
     * @param InMemoryAttributeRepository       $attributeRepository
     * @param InMemoryAttributeGroupRepository  $attributeGroupRepository
     * @param InMemoryAttributeOptionRepository $attributeOptionRepository
     * @param EntityBuilder                     $attributeBuilder
     * @param EntityBuilder                     $attributeGroupBuilder
     * @param EntityBuilder                     $attributeOptionBuilder
     */
    public function __construct(
        InMemoryAttributeRepository $attributeRepository,
        InMemoryAttributeGroupRepository $attributeGroupRepository,
        InMemoryAttributeOptionRepository $attributeOptionRepository,
        EntityBuilder $attributeBuilder,
        EntityBuilder $attributeGroupBuilder,
        EntityBuilder $attributeOptionBuilder
    ) {
        $this->attributeRepository = $attributeRepository;
        $this->attributeGroupRepository = $attributeGroupRepository;
        $this->attributeOptionRepository = $attributeOptionRepository;
        $this->attributeBuilder = $attributeBuilder;
        $this->attributeGroupBuilder = $attributeGroupBuilder;
        $this->attributeOptionBuilder = $attributeOptionBuilder;
    }

    /**
     * @param string $attributeCode
     * @param string $optionCodes
     *
     * @Given /^a simple select attribute ([^"]*) with options? (.*)$/
     */
    public function createAttribute(string $attributeCode, string $optionCodes): void
    {
        $attributeGroup = $this->attributeGroupRepository->findOneByIdentifier(static::DEFAULT_ATTRIBUTE_GROUP);

        if (null === $attributeGroup) {
            $attributeGroup = $this->attributeGroupBuilder->build(['code' => static::DEFAULT_ATTRIBUTE_GROUP]);
            $this->attributeGroupRepository->save($attributeGroup);
        }

        $options = new ListOfCodes($optionCodes);

        $attribute = $this->attributeBuilder->build([
            'code' => $attributeCode,
            'type' => AttributeTypes::OPTION_SIMPLE_SELECT,
            'group' => $attributeGroup->getCode(),
        ]);

        $this->attributeRepository->save($attribute);

        foreach ($options->explode(', ') as $optionCode) {
            $attributeOption = $this->attributeOptionBuilder->build([
                'code' => $optionCode,
                'attribute' => $attribute->getCode(),
            ]);
            $this->attributeOptionRepository->save($attributeOption);
        }
    }
}
