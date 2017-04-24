<?php

namespace spec\Pim\Bundle\EnrichBundle\Normalizer;

use Akeneo\Component\Versioning\Model\Version;
use Doctrine\Common\Collections\ArrayCollection;
use PhpSpec\ObjectBehavior;
use Pim\Bundle\EnrichBundle\Provider\StructureVersion\StructureVersionProviderInterface;
use Pim\Bundle\VersioningBundle\Manager\VersionManager;
use Pim\Component\Catalog\Localization\Localizer\AttributeConverterInterface;
use Pim\Component\Catalog\Model\GroupInterface;
use Pim\Component\Catalog\Model\GroupTypeInterface;
use Pim\Component\Catalog\Model\ProductInterface;
use Pim\Component\Enrich\Converter\ConverterInterface;
use Prophecy\Promise\ReturnPromise;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class GroupNormalizerSpec extends ObjectBehavior
{
    function let(
        NormalizerInterface $normalizer,
        StructureVersionProviderInterface $structureVersionProvider,
        VersionManager $versionManager,
        NormalizerInterface $versionNormalizer,
        AttributeConverterInterface $localizedConverter,
        ConverterInterface $converter
    ) {
        $this->beConstructedWith(
            $normalizer,
            $structureVersionProvider,
            $versionManager,
            $versionNormalizer,
            $localizedConverter,
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
        $localizedConverter,
        $converter,
        GroupInterface $tshirt,
        GroupTypeInterface $groupType,
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

        $tshirt->getType()->willReturn($groupType);
        $groupType->isVariant()->willReturn(true);

        $variantNormalized = [
            'code' => 'my_variant',
            'axis' => ['color', 'size'],
            'type' => 'variant',
            'values' => [
                'number' => ['amount' => 12.5000, 'locale' => null, 'scope' => null],
                'metric' => ['amount' => 12.5000, 'locale' => null, 'scope' => null],
                'prices' => ['amount' => 12.5, 'locale' => null, 'scope' => null],
                'date'   => ['data' => '2015-01-31', 'locale' => null, 'scope' => null],
            ]
        ];

        $valuesLocalized = [
            'number' => ['amount' => '12,5000', 'locale' => null, 'scope' => null],
            'metric' => ['amount' => '12,5000', 'locale' => null, 'scope' => null],
            'prices' => ['amount' => '12,50', 'locale' => null, 'scope' => null],
            'date'   => ['data' => '31/01/2015', 'locale' => null, 'scope' => null],
        ];

        $normalizer->normalize($tshirt, 'standard', $options)->willReturn($variantNormalized);
        $localizedConverter->convertToLocalizedFormats($variantNormalized['values'], $options)
            ->willReturn($valuesLocalized);
        $converter->convert($valuesLocalized)->willReturn($valuesLocalized);

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
                'code'     => 'my_variant',
                'axis'     => ['color', 'size'],
                'type'     => 'variant',
                'values'   => $valuesLocalized,
                'products' => [42],
                'meta'     => [
                    'id'                => 12,
                    'form'              => 'pim-variant-group-edit-form',
                    'structure_version' => 1,
                    'model_type'        => 'variant_group',
                    'created'           => 'normalized_oldest_log',
                    'updated'           => 'normalized_newest_log',
                ]
            ]
        );
    }
}
