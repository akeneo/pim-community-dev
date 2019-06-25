<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2018 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Specification\Akeneo\Pim\Automation\FranklinInsights\Domain\AttributeMapping\Model\Read;

use Akeneo\Pim\Automation\FranklinInsights\Domain\AttributeMapping\Model\AttributeMappingStatus;
use Akeneo\Pim\Automation\FranklinInsights\Domain\AttributeMapping\Model\Read\AttributeMapping;
use Akeneo\Pim\Automation\FranklinInsights\Domain\AttributeMapping\Model\Read\AttributeMappingCollection;
use Akeneo\Pim\Automation\FranklinInsights\Domain\Common\ValueObject\AttributeCode;
use PhpSpec\ObjectBehavior;
use Webmozart\Assert\Assert;

/**
 * @author Julian Prud'homme <julian.prudhomme@akeneo.com>
 */
class AttributeMappingCollectionSpec extends ObjectBehavior
{
    public function it_is_an_attributes_mapping_colletion(): void
    {
        $this->shouldHaveType(AttributeMappingCollection::class);
    }

    public function it_is_traversable(): void
    {
        $this->shouldHaveType(\Traversable::class);

        $this->getIterator()->shouldReturnAnInstanceOf(\Iterator::class);
    }

    public function it_can_check_if_attribute_exists(): void
    {
        $this->hasPimAttribute(new AttributeCode('color'))->shouldReturn(false);

        $this->addAttribute(new AttributeMapping('franklin_color', null, 'text', 'pim_color', AttributeMappingStatus::ATTRIBUTE_ACTIVE, null));
        $this->hasPimAttribute(new AttributeCode('pim_color'))->shouldReturn(true);
        $this->hasPimAttribute(new AttributeCode('burger'))->shouldReturn(false);
    }

    public function it_sorts_attributes_mapping(): void
    {
        $attrDescription = new AttributeMapping('description', 'Description', 'text', null, AttributeMappingStatus::ATTRIBUTE_INACTIVE);
        $attrWeight = new AttributeMapping('weight', 'Weight', 'metric', null, AttributeMappingStatus::ATTRIBUTE_PENDING);
        $attrSize = new AttributeMapping('size', 'Size', 'select', 'pim_size', AttributeMappingStatus::ATTRIBUTE_ACTIVE);
        $attrColor = new AttributeMapping('color', 'Color', 'select', null, AttributeMappingStatus::ATTRIBUTE_PENDING);
        $attrLabel = new AttributeMapping('label', 'Label', 'text', null, AttributeMappingStatus::ATTRIBUTE_INACTIVE);
        $attrHeight = new AttributeMapping('height', 'Height', 'metric', null, AttributeMappingStatus::ATTRIBUTE_ACTIVE);

        $this
            ->addAttribute($attrDescription)
            ->addAttribute($attrWeight)
            ->addAttribute($attrSize)
            ->addAttribute($attrColor)
            ->addAttribute($attrLabel)
            ->addAttribute($attrHeight);

        $franklinAttrCodes = [];
        foreach ($this->getIterator()->getWrappedObject() as $attrMapping) {
            $franklinAttrCodes[] = $attrMapping;
        }
        Assert::eq($franklinAttrCodes, [$attrColor, $attrWeight, $attrHeight, $attrSize, $attrDescription, $attrLabel]);
    }

    public function it_can_check_if_mapping_is_empty(): void
    {
        $this->isEmpty()->shouldReturn(true);

        $attrWeight = new AttributeMapping('weight', 'Weight', 'metric', null, AttributeMappingStatus::ATTRIBUTE_PENDING);
        $this->addAttribute($attrWeight);

        $this->isEmpty()->shouldReturn(false);
    }

    public function it_gets_pending_attributes_franklin_labels()
    {
        $attrWeight = new AttributeMapping('weight', 'Weight', 'metric', null, AttributeMappingStatus::ATTRIBUTE_PENDING);
        $attrSize = new AttributeMapping('size', 'Size', 'select', 'pim_size', AttributeMappingStatus::ATTRIBUTE_ACTIVE);
        $attrColor = new AttributeMapping('color', 'Color', 'select', null, AttributeMappingStatus::ATTRIBUTE_PENDING);
        $attrLabel = new AttributeMapping('label', 'Label', 'text', null, AttributeMappingStatus::ATTRIBUTE_INACTIVE);
        $attrHeight = new AttributeMapping('height', 'Height', 'metric', null, AttributeMappingStatus::ATTRIBUTE_ACTIVE);

        $this
            ->addAttribute($attrWeight)
            ->addAttribute($attrSize)
            ->addAttribute($attrColor)
            ->addAttribute($attrLabel)
            ->addAttribute($attrHeight);

        Assert::eq(array_values($this->getWrappedObject()->getPendingAttributesFranklinLabels()), ['Color', 'Weight']);
    }
}
