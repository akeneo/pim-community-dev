<?php

namespace Specification\Akeneo\Pim\Enrichment\Component\Product\Normalizer\InternalApi;

use Akeneo\Pim\Enrichment\Component\Product\Converter\ConverterInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\GroupInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\Platform\Bundle\UIBundle\Provider\StructureVersion\StructureVersionProviderInterface;
use Akeneo\Tool\Bundle\VersioningBundle\Manager\VersionManager;
use Akeneo\Tool\Component\Versioning\Model\Version;
use Doctrine\Common\Collections\ArrayCollection;
use PhpSpec\ObjectBehavior;
use Prophecy\Promise\ReturnPromise;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class GroupNormalizerSpec extends ObjectBehavior
{
    function let(
        NormalizerInterface $normalizer,
        StructureVersionProviderInterface $structureVersionProvider,
        VersionManager $versionManager,
        NormalizerInterface $versionNormalizer,
        ConverterInterface $converter
    ) {
        $this->beConstructedWith(
            $normalizer,
            $structureVersionProvider,
            $versionManager,
            $versionNormalizer,
            $converter
        );
    }

    function it_supports_groups(GroupInterface $tshirt)
    {
        $this->supportsNormalization($tshirt, 'internal_api')->shouldReturn(true);
    }

    function it_normalizes_groups(
        $normalizer,
        $structureVersionProvider,
        $versionManager,
        $versionNormalizer,
        GroupInterface $tshirt,
        Version $oldestLog,
        Version $newestLog,
        ArrayCollection $products,
        ProductInterface $product,
        \ArrayIterator $productsIterator
    ) {
        $options = [
            'decimal_separator' => ',',
            'date_format'       => 'dd/MM/yyyy',
        ];

        $groupNormalized = [
            'code' => 'my_group',
            'type' => 'related',
        ];

        $normalizer->normalize($tshirt, 'standard', $options)->willReturn($groupNormalized);

        $structureVersionProvider->getStructureVersion()->willReturn(1);
        $versionManager->getOldestLogEntry($tshirt)->willReturn($oldestLog);
        $versionManager->getNewestLogEntry($tshirt)->willReturn($newestLog);
        $versionNormalizer->normalize($oldestLog, 'internal_api')->willReturn('normalized_oldest_log');
        $versionNormalizer->normalize($newestLog, 'internal_api')->willReturn('normalized_newest_log');

        $products->getIterator()->willReturn($productsIterator);
        $productsIterator->rewind()->shouldBeCalled();
        $productsCount = 1;
        $productsIterator->valid()->will(
            function () use (&$productsCount) {
                return $productsCount-- > 0;
            }
        );
        $productsIterator->next()->shouldBeCalled();
        $productsIterator->current()->will(new ReturnPromise([$product]));

        $product->getIdentifier()->willReturn(42);
        $tshirt->getId()->willReturn(12);
        $tshirt->getProducts()->willReturn($products);

        $this->normalize($tshirt, 'internal_api', $options)->shouldReturn(
            [
                'code'     => 'my_group',
                'type'     => 'related',
                'products' => [42],
                'meta'     => [
                    'id'                => 12,
                    'form'              => 'pim-group-edit-form',
                    'structure_version' => 1,
                    'model_type'        => 'group',
                    'created'           => 'normalized_oldest_log',
                    'updated'           => 'normalized_newest_log',
                ]
            ]
        );
    }
}
