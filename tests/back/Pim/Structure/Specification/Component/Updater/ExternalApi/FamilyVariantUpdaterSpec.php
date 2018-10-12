<?php

namespace Specification\Akeneo\Pim\Structure\Component\Updater\ExternalApi;

use Akeneo\Tool\Component\StorageUtils\Exception\ImmutablePropertyException;
use Akeneo\Tool\Component\StorageUtils\Exception\InvalidPropertyTypeException;
use Akeneo\Tool\Component\StorageUtils\Exception\MandatoryPropertyException;
use Akeneo\Tool\Component\StorageUtils\Exception\PropertyException;
use Akeneo\Tool\Component\StorageUtils\Updater\ObjectUpdaterInterface;
use Doctrine\Common\Collections\ArrayCollection;
use Gedmo\Exception;
use PhpSpec\ObjectBehavior;
use Akeneo\Tool\Component\Api\Exception\DocumentedHttpException;
use Akeneo\Pim\Structure\Component\Updater\ExternalApi\FamilyVariantUpdater;
use Akeneo\Pim\Structure\Component\Model\FamilyVariantInterface;
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
        $familyVariant->getId()->willReturn(1);
        $familyVariant->getNumberOfLevel()->willReturn(1);
        $familyUpdater->update($familyVariant, $data, $options)->shouldBeCalled();

        $this
            ->shouldNotThrow(\Exception::class)
            ->during('update', [$familyVariant, $dataToUpdate, $options]);
    }

    function it_throws_an_exception_if_not_same_number_of_level_when_updating($familyUpdater, FamilyVariantInterface $familyVariant)
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
        $options = ['familyCode' => 'boots'];
        $familyVariant->getId()->willReturn(1);
        $familyVariant->getNumberOfLevel()->willReturn(2);
        $familyUpdater->update(Argument::cetera())->shouldNotBeCalled();

        $this
            ->shouldThrow(ImmutablePropertyException::class)
            ->during('update', [$familyVariant, $dataToUpdate, $options]);
    }

    function it_throws_an_exception_if_level_field_is_missing_in_attribute_set($familyUpdater, FamilyVariantInterface $familyVariant)
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
                ],

            ],
        ];
        $options = ['familyCode' => 'boots'];
        $familyVariant->getId()->willReturn(1);
        $familyUpdater->update(Argument::cetera())->shouldNotBeCalled();

        $this
            ->shouldThrow(MandatoryPropertyException::class)
            ->during('update', [$familyVariant, $dataToUpdate, $options]);
    }

    function it_throws_a_property_exception_if_family_field_is_filled(
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
            ->shouldThrow(PropertyException::class)
            ->during('update', [$familyVariant, $data, $options]);
    }

    function it_does_not_throw_exception_if_attribute_sets_is_not_an_array_of_array(
        FamilyVariantInterface $familyVariant
    ) {

        $data = [
            'variant_attribute_sets' => ['foo'],
        ];

        $this
            ->shouldNotThrow(\Exception::class)
            ->during('update', [$familyVariant, $data]);
    }
}
