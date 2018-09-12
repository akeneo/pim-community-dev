<?php

namespace spec\Oro\Bundle\PimDataGridBundle\Normalizer;

use Doctrine\Common\Collections\ArrayCollection;
use PhpSpec\ObjectBehavior;
use Oro\Bundle\PimDataGridBundle\Normalizer\FamilyVariantNormalizer;
use Akeneo\Pim\Structure\Component\Model\FamilyInterface;
use Akeneo\Pim\Structure\Component\Model\FamilyVariantInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class FamilyVariantNormalizerSpec extends ObjectBehavior
{
    function let(NormalizerInterface $translationNormalizer)
    {
        $this->beConstructedWith($translationNormalizer);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(FamilyVariantNormalizer::class);
    }

    function it_is_a_normalizer()
    {
        $this->shouldImplement(NormalizerInterface::class);
    }

    function it_supports_datagrid_format_and_family_variant(FamilyVariantInterface $familyVariant)
    {
        $this->supportsNormalization($familyVariant, 'datagrid')->shouldReturn(true);
        $this->supportsNormalization($familyVariant, 'other_format')->shouldReturn(false);
        $this->supportsNormalization(new \stdClass(), 'other_format')->shouldReturn(false);
        $this->supportsNormalization(new \stdClass(), 'datagrid')->shouldReturn(false);
    }

    function it_normalizes_a_family_variant(
        FamilyVariantInterface $familyVariant,
        FamilyInterface $shoe,
        $translationNormalizer
    ) {
        $translationNormalizer->normalize($familyVariant, 'standard', [])->willReturn([
            'en_US' => 'Size'
        ]);

        $familyVariant->getId()->willReturn(12);
        $familyVariant->getCode()->willReturn('shoes_by_size');
        $familyVariant->getFamily()->willReturn($shoe);
        $shoe->getCode()->willReturn('shoe');
        $familyVariant->getVariantAttributeSets()->willReturn(new ArrayCollection());

        $this->normalize($familyVariant, 'datagrid')->shouldReturn([
            'id'                => 12,
            'familyCode'        => 'shoe',
            'familyVariantCode' => 'shoes_by_size',
            'label'             => 'shoes_by_size',
            'level_1'           => '',
            'level_2'           => '',
        ]);
    }
}
