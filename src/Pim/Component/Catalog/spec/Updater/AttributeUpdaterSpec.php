<?php

namespace spec\Pim\Component\Catalog\Updater;

use Akeneo\Component\StorageUtils\Updater\UnknownPropertyException;
use PhpSpec\ObjectBehavior;
use Pim\Bundle\CatalogBundle\AttributeType\AttributeTypeInterface;
use Pim\Bundle\CatalogBundle\Entity\AttributeTranslation;
use Pim\Component\Catalog\AttributeTypeRegistry;
use Pim\Component\Catalog\Model\AttributeGroupInterface;
use Pim\Component\Catalog\Model\AttributeInterface;
use Pim\Component\Catalog\Model\GroupInterface;
use Pim\Component\Catalog\Repository\AttributeGroupRepositoryInterface;
use Pim\Component\Catalog\Repository\LocaleRepositoryInterface;
use Pim\Component\ReferenceData\ConfigurationRegistryInterface;
use Symfony\Component\PropertyAccess\PropertyAccessor;

class AttributeUpdaterSpec extends ObjectBehavior
{
    function let(
        AttributeGroupRepositoryInterface $attrGroupRepo,
        LocaleRepositoryInterface $localeRepository,
        AttributeTypeRegistry $registry
    ) {
        $this->beConstructedWith($attrGroupRepo, $localeRepository, $registry);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType('Pim\Component\Catalog\Updater\AttributeUpdater');
    }

    function it_is_a_updater()
    {
        $this->shouldImplement('Akeneo\Component\StorageUtils\Updater\ObjectUpdaterInterface');
    }

    function it_updates_a_new_attribute(
        $attrGroupRepo,
        $registry,
        AttributeInterface $attribute,
        AttributeTranslation $translation,
        AttributeGroupInterface $attributeGroup,
        PropertyAccessor $accessor,
        AttributeTypeInterface $attributeType
    ) {
        $attribute->getId()->willReturn(null);
        $attribute->getAttributeType()->willReturn('pim_reference_data_multiselect');

        $data = [
            'labels' => ['en_US' => 'Test1', 'fr_FR' => 'Test2'],
            'group' => 'marketing',
            'attribute_type' => 'pim_catalog_text',
            'date_min' => '2016-12-12T00:00:00+01:00'
        ];

        $attribute->setLocale('en_US')->shouldBeCalled();
        $attribute->setLocale('fr_FR')->shouldBeCalled();
        $attribute->getTranslation()->willReturn($translation);

        $translation->setLabel('Test1')->shouldBeCalled();
        $translation->setLabel('Test2')->shouldBeCalled();

        $attrGroupRepo->findOneByIdentifier('marketing')->willReturn($attributeGroup);
        $attribute->setGroup($attributeGroup)->shouldBeCalled();
        $attribute->setAttributeType('pim_catalog_text')->shouldBeCalled();
        $attribute->setBackendType('backend')->shouldBeCalled();
        $attribute->setUnique(true)->shouldBeCalled();
        $attribute->setDateMin(new \DateTime('2016-12-12T00:00:00+01:00'))->shouldBeCalled();

        $registry->get('pim_catalog_text')->willReturn($attributeType);
        $attributeType->getName()->willReturn('pim_catalog_text');
        $attributeType->getBackendType()->willReturn('backend');
        $attributeType->isUnique()->willReturn(true);

        $accessor->setValue($attribute, 'attribute_type', 'pim_catalog_text');

        $this->update($attribute, $data);
    }

    function it_throws_an_exception_if_no_groups_found(
        $attrGroupRepo,
        $registry,
        AttributeInterface $attribute,
        AttributeTranslation $translation,
        AttributeTypeInterface $attributeType
    ) {
        $attribute->getId()->willReturn(null);
        $attribute->getAttributeType()->willReturn('pim_reference_data_simpleselect');

        $data = [
            'labels' => ['en_US' => 'Test1', 'fr_FR' => 'Test2'],
            'group' => 'marketing',
            'attribute_type' => 'pim_catalog_text'
        ];

        $attribute->setLocale('en_US')->shouldBeCalled();
        $attribute->setLocale('fr_FR')->shouldBeCalled();
        $attribute->getTranslation()->willReturn($translation);

        $translation->setLabel('Test1')->shouldBeCalled();
        $translation->setLabel('Test2')->shouldBeCalled();

        $attrGroupRepo->findOneByIdentifier('marketing')->willReturn(null);
        $registry->get('pim_catalog_text')->willReturn($attributeType);
        $attributeType->getName()->willReturn('pim_catalog_text');
        $attributeType->getBackendType()->willReturn('backend');
        $attributeType->isUnique()->willReturn(true);

        $this->shouldThrow(new \InvalidArgumentException('AttributeGroup "marketing" does not exist'))->during(
            'update',
            [$attribute, $data]
        );
    }

    function it_throws_an_exception_if_it_is_not_an_attribute(GroupInterface $group)
    {
        $this->shouldThrow('\InvalidArgumentException')->during('update', [$group, []]);
    }

    function it_throws_an_exception_if_data_is_not_a_date(AttributeInterface $attribute)
    {
        $this->shouldThrow(new \InvalidArgumentException('Invalid date, "not a date" given'))->during(
            'update',
            [$attribute, ['date_min' => 'not a date']]
        );
    }

    function it_throws_an_exception_if_date_is_invalid(AttributeInterface $attribute)
    {
        $this->shouldThrow(new \InvalidArgumentException('Invalid date, "45/45/2016" given'))->during(
            'update',
            [$attribute, ['date_min' => '45/45/2016']]
        );
    }

    function it_throws_an_exception_if_date_is_not_well_formatted(AttributeInterface $attribute)
    {
        $this->shouldThrow(new \InvalidArgumentException('Attribute expects a string with the format "yyyy-mm-dd" as data, "2016/12/12" given'))->during(
            'update',
            [$attribute, ['date_min' => '2016/12/12']]
        );
    }

    function it_throws_an_exception_when_trying_to_update_a_non_existent_field(AttributeInterface $attribute) {
        $values = [
            'non_existent_field' => 'field',
            'labels' => ['en_US' => 'Test1', 'fr_FR' => 'Test2'],
            'group' => 'marketing',
            'attribute_type' => 'pim_catalog_text'
        ];

        $this
            ->shouldThrow(new UnknownPropertyException('non_existent_field'))
            ->during('update', [$attribute, $values, []]);
    }
}
