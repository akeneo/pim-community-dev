<?php

namespace spec\Pim\Component\Catalog\Updater;

use PhpSpec\ObjectBehavior;
use Pim\Bundle\CatalogBundle\Entity\AttributeTranslation;
use Pim\Bundle\CatalogBundle\Model\AttributeGroupInterface;
use Pim\Bundle\CatalogBundle\Model\AttributeInterface;
use Pim\Bundle\CatalogBundle\Model\GroupInterface;
use Pim\Bundle\CatalogBundle\Repository\AttributeGroupRepositoryInterface;
use Pim\Bundle\CatalogBundle\Repository\LocaleRepositoryInterface;
use Pim\Component\ReferenceData\ConfigurationRegistryInterface;
use Symfony\Component\PropertyAccess\PropertyAccessor;

class AttributeUpdaterSpec extends ObjectBehavior
{
    function let(
        AttributeGroupRepositoryInterface $attrGroupRepo,
        ConfigurationRegistryInterface $registry,
        LocaleRepositoryInterface $localeRepository
    )
    {
        $this->beConstructedWith(
            $attrGroupRepo,
            ['pim_reference_data_multiselect', 'pim_reference_data_simpleselect'],
            $localeRepository,
            $registry
        );
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
        AttributeInterface $attribute,
        AttributeTranslation $translation,
        AttributeGroupInterface $attributeGroup,
        PropertyAccessor $accessor
    ) {
        $attribute->getId()->willReturn(null);
        $attribute->getAttributeType()->willReturn('pim_reference_data_multiselect');

        $data = [
            'labels' => ['en_US' => 'Test1', 'fr_FR' => 'Test2'],
            'group' => 'marketing',
            'attributeType' => 'pim_catalog_text'
        ];

        $attribute->setLocale('en_US')->shouldBeCalled();
        $attribute->setLocale('fr_FR')->shouldBeCalled();
        $attribute->getTranslation()->willReturn($translation);

        $translation->setLabel('Test1')->shouldBeCalled();
        $translation->setLabel('Test2')->shouldBeCalled();

        $attrGroupRepo->findOneByIdentifier('marketing')->willReturn($attributeGroup);
        $attribute->setGroup($attributeGroup)->shouldBeCalled();
        $attribute->setAttributeType('pim_catalog_text')->shouldBeCalled();

        $accessor->setValue($attribute, 'attributeType', 'pim_catalog_text');

        $this->update($attribute, $data);
    }

    function it_throws_an_exception_if_no_groups_found(
        $attrGroupRepo,
        AttributeInterface $attribute,
        AttributeTranslation $translation
    ) {
        $attribute->getId()->willReturn(null);
        $attribute->getAttributeType()->willReturn('pim_reference_data_simpleselect');

        $data = [
            'labels' => ['en_US' => 'Test1', 'fr_FR' => 'Test2'],
            'group' => 'marketing',
            'attributeType' => 'pim_catalog_text'
        ];

        $attribute->setLocale('en_US')->shouldBeCalled();
        $attribute->setLocale('fr_FR')->shouldBeCalled();
        $attribute->getTranslation()->willReturn($translation);

        $translation->setLabel('Test1')->shouldBeCalled();
        $translation->setLabel('Test2')->shouldBeCalled();

        $attrGroupRepo->findOneByIdentifier('marketing')->willReturn(null);

        $this->shouldThrow(new \InvalidArgumentException('AttributeGroup "marketing" does not exist'))->during(
            'update',
            [$attribute, $data]
        );
    }

    function it_throws_an_exception_if_it_is_not_an_attribute(GroupInterface $group)
    {
        $this->shouldThrow('\InvalidArgumentException')->during('update', [$group, []]);
    }
}
