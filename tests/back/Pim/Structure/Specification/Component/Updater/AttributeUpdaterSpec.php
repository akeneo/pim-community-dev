<?php

namespace Specification\Akeneo\Pim\Structure\Component\Updater;

use Akeneo\Pim\Structure\Component\Updater\AttributeUpdater;
use Akeneo\Tool\Component\StorageUtils\Updater\ObjectUpdaterInterface;
use Akeneo\Tool\Component\Localization\TranslatableUpdater;
use Akeneo\Tool\Component\StorageUtils\Exception\InvalidObjectException;
use Akeneo\Tool\Component\StorageUtils\Exception\InvalidPropertyException;
use Akeneo\Tool\Component\StorageUtils\Exception\InvalidPropertyTypeException;
use Akeneo\Tool\Component\StorageUtils\Exception\UnknownPropertyException;
use PhpSpec\ObjectBehavior;
use Akeneo\Pim\Structure\Component\AttributeTypeInterface;
use Akeneo\Pim\Structure\Component\AttributeTypeRegistry;
use Akeneo\Pim\Structure\Component\Model\AttributeGroupInterface;
use Akeneo\Pim\Structure\Component\Model\AttributeInterface;
use Akeneo\Pim\Structure\Component\Repository\AttributeGroupRepositoryInterface;
use Akeneo\Channel\Component\Repository\LocaleRepositoryInterface;
use Symfony\Component\PropertyAccess\Exception\NoSuchPropertyException;
use Symfony\Component\PropertyAccess\PropertyAccessor;

class AttributeUpdaterSpec extends ObjectBehavior
{
    function let(
        AttributeGroupRepositoryInterface $attrGroupRepo,
        LocaleRepositoryInterface $localeRepository,
        AttributeTypeRegistry $registry,
        TranslatableUpdater $translatableUpdater
    ) {
        $this->beConstructedWith(
            $attrGroupRepo,
            $localeRepository,
            $registry,
            $translatableUpdater,
            ['auto_option_sorting']
        );
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(AttributeUpdater::class);
    }

    function it_is_a_updater()
    {
        $this->shouldImplement(ObjectUpdaterInterface::class);
    }

    function it_updates_attribute_properties(AttributeInterface $attribute)
    {
        $attribute->setProperty('auto_option_sorting', true)->shouldBeCalled();
        $this->update($attribute, ['auto_option_sorting' => true]);
    }

    function it_throw_an_exception_when_trying_to_update_anything_else_than_an_attribute()
    {
        $this->shouldThrow(
            InvalidObjectException::objectExpected(
                'stdClass',
                AttributeInterface::class
            )
        )->during(
            'update',
            [new \stdClass(), []]
        );
    }

    function it_updates_a_new_attribute(
        $attrGroupRepo,
        $registry,
        $translatableUpdater,
        AttributeInterface $attribute,
        AttributeGroupInterface $attributeGroup,
        PropertyAccessor $accessor,
        AttributeTypeInterface $attributeType
    ) {
        $attribute->getId()->willReturn(null);
        $attribute->getType()->willReturn('pim_reference_data_multiselect');

        $data = [
            'labels' => ['en_US' => 'Test1', 'fr_FR' => 'Test2'],
            'group' => 'marketing',
            'type' => 'pim_catalog_text',
            'date_min' => '2016-12-12T00:00:00+01:00'
        ];

        $translatableUpdater->update($attribute, ['en_US' => 'Test1', 'fr_FR' => 'Test2']);

        $attrGroupRepo->findOneByIdentifier('marketing')->willReturn($attributeGroup);
        $attribute->setGroup($attributeGroup)->shouldBeCalled();
        $attribute->setType('pim_catalog_text')->shouldBeCalled();
        $attribute->setBackendType('backend')->shouldBeCalled();
        $attribute->setUnique(true)->shouldBeCalled();
        $attribute->setDateMin(new \DateTime('2016-12-12T00:00:00+01:00'))->shouldBeCalled();

        $registry->get('pim_catalog_text')->willReturn($attributeType);
        $attributeType->getName()->willReturn('pim_catalog_text');
        $attributeType->getBackendType()->willReturn('backend');
        $attributeType->isUnique()->willReturn(true);

        $accessor->setValue($attribute, 'type', 'pim_catalog_text');

        $this->update($attribute, $data);
    }

    function it_throws_an_exception_if_no_groups_found(
        $attrGroupRepo,
        $registry,
        $translatableUpdater,
        AttributeInterface $attribute,
        AttributeTypeInterface $attributeType
    ) {
        $attribute->getId()->willReturn(null);
        $attribute->getType()->willReturn('pim_reference_data_simpleselect');

        $data = [
            'labels' => ['en_US' => 'Test1', 'fr_FR' => 'Test2'],
            'group' => 'marketing',
            'type' => 'pim_catalog_text'
        ];

        $translatableUpdater->update($attribute, ['en_US' => 'Test1', 'fr_FR' => 'Test2']);

        $attrGroupRepo->findOneByIdentifier('marketing')->willReturn(null);
        $registry->get('pim_catalog_text')->willReturn($attributeType);
        $attributeType->getName()->willReturn('pim_catalog_text');
        $attributeType->getBackendType()->willReturn('backend');
        $attributeType->isUnique()->willReturn(true);

        $this->shouldThrow(
            InvalidPropertyException::validEntityCodeExpected(
                'group',
                'code',
                'The attribute group does not exist',
                AttributeUpdater::class,
                'marketing'
            )
        )->during(
            'update',
            [$attribute, $data]
        );
    }

    function it_throws_an_exception_if_it_attribute_type_is_empty(AttributeInterface $attribute)
    {
        $this->shouldThrow(
            InvalidPropertyException::valueNotEmptyExpected('type',
                AttributeUpdater::class
            )
        )->during(
            'update',
            [$attribute, ['type' => '']]
        );
    }

    function it_throws_an_exception_if_attribute_type_does_not_exist(AttributeInterface $attribute, $registry)
    {
        $registry->get('unknown_type')->willThrow(new \LogicException());

        $this->shouldThrow(
            InvalidPropertyException::validEntityCodeExpected(
                'type',
                'attribute type',
                'The attribute type does not exist',
                AttributeUpdater::class,
                'unknown_type'
            )
        )->during('update', [$attribute, ['type' => 'unknown_type']]);
    }

    function it_throws_an_exception_if_data_is_not_a_date(AttributeInterface $attribute)
    {
        $this->shouldThrow(
            InvalidPropertyException::dateExpected(
                'date_min',
                'yyyy-mm-dd',
                AttributeUpdater::class,
                'not a date'
            )
        )->during(
            'update',
            [$attribute, ['date_min' => 'not a date']]
        );
    }

    function it_throws_an_exception_if_date_is_invalid(AttributeInterface $attribute)
    {
        $this->shouldThrow(
            InvalidPropertyException::dateExpected(
                'date_min',
                'yyyy-mm-dd',
                AttributeUpdater::class,
                '45/45/2016'
            )
        )->during(
            'update',
            [$attribute, ['date_min' => '45/45/2016']]
        );
    }

    function it_throws_an_exception_if_date_is_not_well_formatted(AttributeInterface $attribute)
    {
        $this->shouldThrow(
            InvalidPropertyException::dateExpected(
                'date_min',
                'yyyy-mm-dd',
                AttributeUpdater::class,
                '2016/12/12'
            )
        )->during(
            'update',
            [$attribute, ['date_min' => '2016/12/12']]
        );
    }

    function it_throws_an_exception_when_trying_to_update_a_non_existent_field(AttributeInterface $attribute) {
        $values = [
            'non_existent_field' => 'field',
            'labels' => ['en_US' => 'Test1', 'fr_FR' => 'Test2'],
            'group' => 'marketing',
            'type' => 'pim_catalog_text'
        ];

        $this
            ->shouldThrow(UnknownPropertyException::unknownProperty('non_existent_field', new NoSuchPropertyException()))
            ->during('update', [$attribute, $values, []]);
    }

    function it_throws_an_exception_if_locale_does_not_exist($localeRepository, AttributeInterface $attribute) {
        $localeRepository->findOneByIdentifier('foo')->willReturn(null);

        $values = [
            'available_locales' => ['foo']
        ];

        $this->shouldThrow(
            InvalidPropertyException::validEntityCodeExpected(
                'available_locales',
                'locale code',
                'The locale does not exist',
                AttributeUpdater::class,
                'foo'
            )
        )->during('update', [$attribute, $values, []]);
    }

    function it_throws_an_exception_when_code_is_not_scalar(AttributeInterface $attribute)
    {
        $values = [
            'code' => [],
        ];

        $this
            ->shouldThrow(
                InvalidPropertyTypeException::scalarExpected('code',
                    AttributeUpdater::class, [])
            )
            ->during('update', [$attribute, $values, []]);
    }

    function it_throws_an_exception_when_labels_is_not_an_array(AttributeInterface $attribute)
    {
        $values = [
            'labels' => 'not_an_array',
        ];

        $this
            ->shouldThrow(
                InvalidPropertyTypeException::arrayExpected('labels',
                    AttributeUpdater::class, 'not_an_array')
            )
            ->during('update', [$attribute, $values, []]);
    }

    function it_throws_an_exception_when_available_locales_is_not_an_array(AttributeInterface $attribute)
    {
        $values = [
            'available_locales' => 'not_an_array',
        ];

        $this
            ->shouldThrow(
                InvalidPropertyTypeException::arrayExpected('available_locales',
                    AttributeUpdater::class, 'not_an_array')
            )
            ->during('update', [$attribute, $values, []]);
    }

    function it_throws_an_exception_when_allowed_extensions_is_not_an_array(AttributeInterface $attribute)
    {
        $values = [
            'allowed_extensions' => 'not_an_array',
        ];

        $this
            ->shouldThrow(
                InvalidPropertyTypeException::arrayExpected('allowed_extensions',
                    AttributeUpdater::class, 'not_an_array')
            )
            ->during('update', [$attribute, $values, []]);
    }

    function it_sets_the_default_unique_property_when_setting_an_attribute_type(
        AttributeTypeRegistry $registry,
        AttributeInterface $attribute,
        AttributeTypeInterface $textAttributeType
    ) {
        $attribute->isUnique()->willReturn(null);

        $textAttributeType->getName()->willReturn('pim_catalog_text');
        $textAttributeType->getBackendType()->willReturn('text');
        $textAttributeType->isUnique()->willReturn(false);
        $registry->get('pim_catalog_text')->willReturn($textAttributeType);
        $attribute->setType('pim_catalog_text')->shouldBeCalled();
        $attribute->setBackendType('text')->shouldBeCalled();

        $attribute->setUnique(false)->shouldBeCalled();

        $this->update($attribute, ['type' => 'pim_catalog_text']);
    }

    function it_does_not_update_the_unique_property_if_it_is_already_defined(
        AttributeTypeRegistry $registry,
        AttributeInterface $attribute,
        AttributeTypeInterface $textAttributeType
    ) {
        $attribute->isUnique()->willReturn(true);

        $textAttributeType->getName()->willReturn('pim_catalog_text');
        $textAttributeType->getBackendType()->willReturn('text');
        $textAttributeType->isUnique()->willReturn(false);
        $registry->get('pim_catalog_text')->willReturn($textAttributeType);
        $attribute->setType('pim_catalog_text')->shouldBeCalled();
        $attribute->setBackendType('text')->shouldBeCalled();

        $attribute->setUnique(false)->shouldNotBeCalled();

        $this->update($attribute, ['type' => 'pim_catalog_text']);
    }

    function it_sets_the_unique_property_to_true_if_the_attribute_type_must_be_unique(
        AttributeTypeRegistry $registry,
        AttributeInterface $attribute,
        AttributeTypeInterface $identifierAttributeType
    ) {
        $attribute->isUnique()->willReturn(false);

        $identifierAttributeType->getName()->willReturn('pim_catalog_identifier');
        $identifierAttributeType->getBackendType()->willReturn('identifier');
        $identifierAttributeType->isUnique()->willReturn(true);
        $registry->get('pim_catalog_identifier')->willReturn($identifierAttributeType);
        $attribute->setType('pim_catalog_identifier')->shouldBeCalled();
        $attribute->setBackendType('identifier')->shouldBeCalled();

        $attribute->setUnique(true)->shouldBeCalled();

        $this->update($attribute, ['type' => 'pim_catalog_identifier']);
    }
}
