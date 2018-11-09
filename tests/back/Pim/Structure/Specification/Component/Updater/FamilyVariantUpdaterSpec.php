<?php

namespace Specification\Akeneo\Pim\Structure\Component\Updater;

use Akeneo\Channel\Component\Model\ChannelInterface;
use Akeneo\Pim\Structure\Component\Model\AttributeInterface;
use Akeneo\Pim\Structure\Component\Model\CommonAttributeCollection;
use Akeneo\Pim\Structure\Component\Model\FamilyInterface;
use Akeneo\Pim\Structure\Component\Model\FamilyVariantInterface;
use Akeneo\Pim\Structure\Component\Model\VariantAttributeSetInterface;
use Akeneo\Pim\Structure\Component\Updater\FamilyVariantUpdater;
use Akeneo\Tool\Component\Localization\TranslatableUpdater;
use Akeneo\Tool\Component\StorageUtils\Exception\ImmutablePropertyException;
use Akeneo\Tool\Component\StorageUtils\Exception\InvalidObjectException;
use Akeneo\Tool\Component\StorageUtils\Exception\InvalidPropertyException;
use Akeneo\Tool\Component\StorageUtils\Exception\InvalidPropertyTypeException;
use Akeneo\Tool\Component\StorageUtils\Factory\SimpleFactoryInterface;
use Akeneo\Tool\Component\StorageUtils\Repository\IdentifiableObjectRepositoryInterface;
use Akeneo\Tool\Component\StorageUtils\Updater\ObjectUpdaterInterface;
use Doctrine\Common\Collections\Collection;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class FamilyVariantUpdaterSpec extends ObjectBehavior
{
    function let(
        SimpleFactoryInterface $attributeSetFactory,
        TranslatableUpdater $updater,
        IdentifiableObjectRepositoryInterface $familyRepository,
        IdentifiableObjectRepositoryInterface $attributeRepository
    ) {
        $this->beConstructedWith($attributeSetFactory, $updater, $familyRepository, $attributeRepository);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(FamilyVariantUpdater::class);
    }

    function it_is_an_updater()
    {
        $this->shouldImplement(ObjectUpdaterInterface::class);
    }

    function it_updates_a_new_family_variant(
        $updater,
        $attributeSetFactory,
        $familyRepository,
        $attributeRepository,
        FamilyVariantInterface $familyVariant,
        FamilyInterface $family,
        VariantAttributeSetInterface $attributeSet1,
        VariantAttributeSetInterface $attributeSet2,
        VariantAttributeSetInterface $commonAttributeSet,
        AttributeInterface $name,
        AttributeInterface $size,
        AttributeInterface $color,
        AttributeInterface $description,
        AttributeInterface $sku,
        AttributeInterface $other,
        Collection $axes1,
        Collection $axes2,
        CommonAttributeCollection $commonAttributes,
        \Iterator $commonAttributesIterator
    ) {
        $familyRepository->findOneByIdentifier('t-shirt')->willReturn($family);

        $familyVariant->getId()->willReturn(42);
        $familyVariant->getNumberOfLevel()->willReturn(2);
        $familyVariant->getFamily()->willReturn($family);
        $family->getAttributeCodes()->willReturn(
            ['name', 'size', 'description', 'color', 'sku']
        );

        $attributeRepository->findOneByIdentifier('name')->willreturn($name);
        $attributeRepository->findOneByIdentifier('size')->willreturn($size);
        $attributeRepository->findOneByIdentifier('color')->willreturn($color);
        $attributeRepository->findOneByIdentifier('description')->willreturn($description);
        $attributeRepository->findOneByIdentifier('sku')->willreturn($sku);
        $attributeRepository->findOneByIdentifier('other')->willreturn($other);

        $familyVariant->setCode('my-tshirt')->shouldBeCalled();
        $familyVariant->setFamily($family)->shouldBeCalled();
        $familyVariant->getVariantAttributeSet(Argument::any())->willReturn(null);
        $updater->update($familyVariant, ['en_US' => 'My tshirt'])->shouldBeCalled();

        $attributeSetFactory->create()->willReturn($attributeSet1, $attributeSet2, $commonAttributeSet);

        $familyVariant->addVariantAttributeSet($attributeSet1)->shouldBeCalled();
        $attributeSet1->setAttributes([$description])->shouldBeCalled();
        $attributeSet1->setAxes([$color])->shouldBeCalled();
        $attributeSet1->setLevel(1)->shouldBeCalled();
        $attributeSet1->getAxes()->willReturn($axes1);
        $axes1->isEmpty()->willReturn(true);
        $axes1->map(Argument::any())->shouldNotBeCalled();

        $familyVariant->addVariantAttributeSet($attributeSet2)->shouldBeCalled();
        $attributeSet2->setAttributes([$size, $sku])->shouldBeCalled();
        $attributeSet2->setAxes([$size, $other])->shouldBeCalled();
        $attributeSet2->setLevel(2)->shouldBeCalled();
        $attributeSet2->getAxes()->willReturn($axes2);
        $axes2->isEmpty()->willReturn(true);
        $axes2->map(Argument::any())->shouldNotBeCalled();

        $this->update($familyVariant, [
            'code' => 'my-tshirt',
            'family' => 't-shirt',
            'labels' => [
                'en_US' => 'My tshirt'
            ],
            'variant_attribute_sets' => [
                [
                    'axes' => ['color'],
                    'attributes' => ['description'],
                    'level' => 1,
                ],
                [
                    'axes' => ['size', 'other'],
                    'attributes' => ['size', 'sku'],
                    'level' => 2,
                ]
            ],
        ], []);
    }

    function it_updates_an_existing_family_variant(
        $updater,
        $attributeSetFactory,
        $familyRepository,
        $attributeRepository,
        FamilyVariantInterface $familyVariant,
        FamilyInterface $family,
        VariantAttributeSetInterface $attributeSet1,
        VariantAttributeSetInterface $attributeSet2,
        AttributeInterface $name,
        AttributeInterface $size,
        AttributeInterface $color,
        AttributeInterface $description,
        AttributeInterface $sku,
        AttributeInterface $other,
        Collection $axes1,
        Collection $axes2,
        Collection $axisCodes1,
        Collection $axisCodes2,
        Collection $attributes1,
        \Iterator $attributesIterator1
    ) {
        $familyRepository->findOneByIdentifier('t-shirt')->willReturn($family);

        $familyVariant->getId()->willReturn(42);
        $familyVariant->getNumberOfLevel()->willReturn(2);
        $familyVariant->getFamily()->willReturn($family);
        $family->getAttributeCodes()->willReturn(
            ['name', 'size', 'description', 'color', 'sku']
        );

        $attributeRepository->findOneByIdentifier('name')->willreturn($name);
        $attributeRepository->findOneByIdentifier('size')->willreturn($size);
        $attributeRepository->findOneByIdentifier('color')->willreturn($color);
        $attributeRepository->findOneByIdentifier('description')->willreturn($description);
        $attributeRepository->findOneByIdentifier('sku')->willreturn($sku);
        $attributeRepository->findOneByIdentifier('other')->willreturn($other);

        $familyVariant->setCode('my-tshirt')->shouldBeCalled();
        $familyVariant->setFamily($family)->shouldBeCalled();
        $familyVariant->getVariantAttributeSet(1)->willReturn($attributeSet1);
        $familyVariant->getVariantAttributeSet(2)->willReturn($attributeSet2);
        $updater->update($familyVariant, ['en_US' => 'My tshirt'])->shouldBeCalled();

        $attributeSetFactory->create()->shouldNotBeCalled();

        $axes1->isEmpty()->willReturn(false);
        $axes1->map(Argument::any())->willReturn($axisCodes1);
        $axisCodes1->toArray()->willReturn(['color']);

        $attributeSet1->setAttributes([$description])->shouldBeCalled();
        $attributeSet1->setAxes([$color])->shouldBeCalled();
        $attributeSet1->setLevel(Argument::any())->shouldNotBeCalled();
        $attributeSet1->getAxes()->willReturn($axes1);
        $attributeSet1->getAttributes()->willReturn($attributes1);

        $attributeSet2->getAxes()->willReturn($axes2);
        $axes2->isEmpty()->willReturn(false);
        $axes2->map(Argument::any())->willReturn($axisCodes2);
        $axisCodes2->toArray()->willReturn(['size', 'other']);
        $attributeSet2->setAxes([$size, $other])->shouldBeCalled();

        $attributeSet2->setAttributes([$size, $sku])->shouldBeCalled();
        $attributeSet2->setLevel(Argument::any())->shouldNotBeCalled();
        $familyVariant->addVariantAttributeSet(Argument::any())->shouldNotBeCalled();

        $this->update($familyVariant, [
            'code' => 'my-tshirt',
            'family' => 't-shirt',
            'labels' => [
                'en_US' => 'My tshirt'
            ],
            'variant_attribute_sets' => [
                [
                    'axes' => ['color'],
                    'attributes' => ['description'],
                    'level' => 1,
                ],
                [
                    'axes' => ['size', 'other'],
                    'attributes' => ['size', 'sku'],
                    'level' => 2,
                ]
            ],
        ], []);
    }

    function it_throws_an_exception_if_the_number_of_attribute_set_is_changed(
        FamilyVariantInterface $familyVariant
    ) {
        $familyVariant->getId()->willReturn(42);
        $familyVariant->getNumberOfLevel()->willReturn(1);
        $familyVariant->setCode('my-tshirt')->shouldBeCalled();

        $this->shouldThrow(ImmutablePropertyException::class)->during('update', [
            $familyVariant,
            [
                'code' => 'my-tshirt',
                'variant_attribute_sets' => [
                    [
                        'axes' => ['color'],
                        'attributes' => ['description'],
                        'level' => 1,
                    ],
                    [
                        'axes' => ['size', 'other'],
                        'attributes' => ['size', 'sku'],
                        'level' => 2,
                    ],
                ],
            ],
        ]);
    }

    function it_throws_an_exception_if_the_family_code_is_invalid(
        $familyRepository,
        FamilyVariantInterface $familyVariant
    ) {
        $familyRepository->findOneByIdentifier('t-shirt')->willReturn(null);

        $this->shouldThrow(InvalidPropertyException::class)->during('update', [
            $familyVariant,
            [
                'family' => 't-shirt',
            ],
        ]);
    }

    function it_throws_an_exception_if_code_is_not_string(FamilyVariantInterface $familyVariant)
    {
        $this->shouldThrow(InvalidPropertyTypeException::class)->during('update', [
            $familyVariant,
            [
                'code' => null,
            ],
        ]);
    }

    function it_throws_an_exception_if_labels_are_not_an_array(FamilyVariantInterface $familyVariant)
    {
        $this->shouldThrow(InvalidPropertyTypeException::class)->during('update', [
            $familyVariant,
            [
                'labels' => null,
            ],
        ]);
    }

    function it_throws_an_exception_if_labels_are_not_an_array_of_array(FamilyVariantInterface $familyVariant)
    {
        $this->shouldThrow(InvalidPropertyTypeException::class)->during('update', [
            $familyVariant,
            [
                'labels' => ['fr_FR' => []],
            ],
        ]);
    }

    function it_throws_an_exception_if_variant_attribute_sets_are_not_an_array(FamilyVariantInterface $familyVariant)
    {
        $this->shouldThrow(InvalidPropertyTypeException::class)->during('update', [
            $familyVariant,
            [
                'variant_attribute_sets' => null,
            ],
        ]);
    }

    function it_throws_an_exception_if_variant_attribute_sets_are_not_an_array_of_array(
        FamilyVariantInterface $familyVariant
    ) {
        $this->shouldThrow(InvalidPropertyTypeException::class)->during('update', [
            $familyVariant,
            [
                'variant_attribute_sets' => ['foo'],
            ],
        ]);
    }

    function it_throws_an_exception_if_the_given_object_is_not_a_family_variant(ChannelInterface $channel)
    {
        $this->shouldThrow(InvalidObjectException::class)->during('update', [$channel, []]);
    }
}
