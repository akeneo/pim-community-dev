<?php

namespace spec\Pim\Component\Api\Updater;

use Akeneo\Component\StorageUtils\Updater\ObjectUpdaterInterface;
use PhpSpec\ObjectBehavior;
use Pim\Component\Api\Exception\DocumentedHttpException;
use Pim\Component\Api\Updater\FamilyVariantUpdater;
use Pim\Component\Catalog\Model\FamilyVariantInterface;
use Prophecy\Argument;

class FamilyVariantUpdaterSpec extends ObjectBehavior
{
    function let(ObjectUpdaterInterface $familyUpdater)
    {
        $this->beConstructedWith($familyUpdater);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(FamilyVariantUpdater::class);
    }

    function it_is_an_updater()
    {
        $this->shouldImplement(ObjectUpdaterInterface::class);
    }

    function it_updates_a_family_variant($familyUpdater, FamilyVariantInterface $familyVariant)
    {
        $dataToUpdate = [
            'code' => 'family_variant',
            'labels' => [
                'en_US' => 'My family variant'
            ],
            'variant_attribute_sets' => [
                [
                    'axes' => ['color'],
                    'attributes' => ['description'],
                    'level'=> 1,
                ],

            ],
        ];
        $data = [
            'code' => 'family_variant',
            'family' => 'boots',
            'labels' => [
                'en_US' => 'My family variant'
            ],
            'variant_attribute_sets' => [
                [
                    'axes' => ['color'],
                    'attributes' => ['description'],
                    'level'=> 1,
                ],

            ],
        ];
        $options = ['familyCode' => 'boots'];
        $familyUpdater->update($familyVariant, $data, $options)->shouldBeCalled();

        $this
            ->shouldNotThrow(DocumentedHttpException::class)
            ->during('update', [$familyVariant, $dataToUpdate, $options]);
    }

    function it_throws_a_documented_exception_if_family_field_is_filled(
        $familyUpdater,
        FamilyVariantInterface $familyVariant
    ) {
        $data = [
            'code' => 'family_variant',
            'family' => 'boot',
            'labels' => [
                'en_US' => 'My family variant'
            ],
            'variant_attribute_sets' => [
                [
                    'axes' => ['color'],
                    'attributes' => ['description'],
                    'level'=> 1,
                ],

            ],
        ];
        $options = ['familyCode' => 'boots'];
        $familyUpdater->update(Argument::cetera())->shouldNotBeCalled();

        $this
            ->shouldThrow(DocumentedHttpException::class)
            ->during('update', [$familyVariant, $data, $options]);
    }
}
