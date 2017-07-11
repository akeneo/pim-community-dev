<?php

namespace spec\Pim\Component\Catalog\Updater;

use Akeneo\Component\Localization\TranslatableUpdater;
use Akeneo\Component\StorageUtils\Exception\InvalidObjectException;
use Akeneo\Component\StorageUtils\Exception\InvalidPropertyException;
use Akeneo\Component\StorageUtils\Factory\SimpleFactoryInterface;
use Akeneo\Component\StorageUtils\Repository\IdentifiableObjectRepositoryInterface;
use Akeneo\Component\StorageUtils\Updater\ObjectUpdaterInterface;
use Pim\Component\Catalog\Model\AttributeSetInterface;
use Pim\Component\Catalog\Model\ChannelInterface;
use Pim\Component\Catalog\Model\FamilyInterface;
use Pim\Component\Catalog\Model\FamilyVariantInterface;
use Pim\Component\Catalog\Updater\FamilyVariantUpdater;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class FamilyVariantUpdaterSpec extends ObjectBehavior
{
    function let(
        SimpleFactoryInterface $attributeSetFactory,
        TranslatableUpdater $updater,
        IdentifiableObjectRepositoryInterface $familyRepository
    ) {
        $this->beConstructedWith($attributeSetFactory, $updater, $familyRepository);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(FamilyVariantUpdater::class);
    }

    function it_is_an_updater()
    {
        $this->shouldImplement(ObjectUpdaterInterface::class);
    }

    function it_updates_a_family_variant(
        $updater,
        $attributeSetFactory,
        $familyRepository,
        FamilyVariantInterface $familyVariant,
        FamilyInterface $family,
        AttributeSetInterface $attributeSet1,
        AttributeSetInterface $attributeSet2,
        AttributeSetInterface $commonAttributeSet
    ) {
        $familyRepository->findOneByIdentifier('t-shirt')->willReturn($family);

        $familyVariant->getFamily()->willReturn($family);
        $family->getAttributeCodes()->willReturn(
            ['name', 'picture', 'pictures', 'composition', 'size', 'EAN', 'SKU', 'weight']
        );

        $familyVariant->setCode('my-tshirt')->shouldBeCalled();
        $familyVariant->setFamily($family)->shouldBeCalled();
        $updater->update($familyVariant, ['en_US' => 'My tshirt'])->shouldBeCalled();

        $attributeSetFactory->create()->willReturn($attributeSet1, $attributeSet2, $commonAttributeSet);

        $familyVariant->addVariantAttributeSet(1, $attributeSet1)->shouldBeCalled();
        $attributeSet1->setAxes(['color'])->shouldBeCalled();
        $attributeSet1->setAttributes(['picture', 'pictures', 'composition'])->shouldBeCalled();

        $familyVariant->addVariantAttributeSet(2, $attributeSet2)->shouldBeCalled();
        $attributeSet2->setAxes(['size', 'other'])->shouldBeCalled();
        $attributeSet2->setAttributes(['size', 'EAN', 'SKU', 'weight'])->shouldBeCalled();

        $familyVariant->addCommonAttributeSet($commonAttributeSet)->shouldBeCalled();
        $commonAttributeSet->setAttributes(['name'])->shouldBeCalled();

        $this->update($familyVariant, [
            'code' => 'my-tshirt',
            'family' => 't-shirt',
            'label' => [
                'en_US' => 'My tshirt'
            ],
            'variant-attribute-sets' => [
                [
                    'axes' => ['color'],
                    'attributes' => ['picture', 'pictures', 'composition']
                ],
                [
                    'axes' => ['size', 'other'],
                    'attributes' => ['size', 'EAN', 'SKU', 'weight']
                ]
            ],
        ], []);
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

    function it_throws_an_exception_if_the_given_object_is_not_a_family_variant(ChannelInterface $channel)
    {
        $this->shouldThrow(InvalidObjectException::class)->during('update', [$channel, []]);
    }
}
