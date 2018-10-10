<?php

declare(strict_types=1);

namespace Akeneo\Test\Acceptance\Attribute;

use Akeneo\Pim\Enrichment\Component\Product\Model\AttributeGroupInterface;
use Akeneo\Test\Acceptance\AttributeGroup\InMemoryAttributeGroupRepository;
use Akeneo\Test\Common\EntityBuilder;
use Behat\Behat\Context\Context;
use Behat\Gherkin\Node\TableNode;

class AttributeContext implements Context
{
    /** @var InMemoryAttributeRepository */
    private $attributeRepository;

    /** @var EntityBuilder */
    private $attributeBuilder;

    /** @var InMemoryAttributeGroupRepository */
    private $attributeGroupRepository;

    /** @var EntityBuilder */
    private $attributeGroupBuilder;

    public function __construct(
        InMemoryAttributeRepository $attributeRepository,
        EntityBuilder $attributeBuilder,
        InMemoryAttributeGroupRepository $attributeGroupRepository,
        EntityBuilder $attributeGroupBuilder
    ) {
        $this->attributeRepository = $attributeRepository;
        $this->attributeBuilder = $attributeBuilder;
        $this->attributeGroupRepository = $attributeGroupRepository;
        $this->attributeGroupBuilder = $attributeGroupBuilder;
    }

    /**
     * @Given the following attribute:
     */
    public function theFollowingAttribute(TableNode $table)
    {
        foreach ($table->getHash() as $attributeData) {
            // "group" is mandatory to be able to create a valid attribute entity.
            // But in guerkins, this information can be completely useless and add noise on it
            // that's why we create on the fly a group if this data is missing.
            if (!isset($attributeData['group'])) {
                $group = $this->attributeGroupBuilder->build(['code' => 'MANDATORY_ATTRIBUTE_GROUP_CODE']);
                $this->attributeGroupRepository->save($group);

                $attributeData['group'] = 'MANDATORY_ATTRIBUTE_GROUP_CODE';
            }

            foreach ($attributeData as $index => $value) {
                if ((false !== strpos($index, 'label-') || false !== strpos($index, 'labels-'))) {
                    if ('' !== $value) {
                        $locale = str_replace('label-', '', str_replace('labels-', '', $index));
                        $attributeData['labels'][$locale] = $value;
                    }
                    unset($attributeData[$index]);
                }
            }

            $attribute = $this->attributeBuilder->build($attributeData);

            $this->attributeRepository->save($attribute);
        }
    }
}
