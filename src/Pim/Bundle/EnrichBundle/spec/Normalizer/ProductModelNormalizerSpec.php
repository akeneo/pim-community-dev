<?php

namespace spec\Pim\Bundle\EnrichBundle\Normalizer;

use PhpSpec\ObjectBehavior;
use Pim\Bundle\EnrichBundle\Provider\Form\FormProviderInterface;
use Pim\Bundle\VersioningBundle\Manager\VersionManager;
use Pim\Component\Catalog\Localization\Localizer\AttributeConverterInterface;
use Pim\Component\Catalog\Model\FamilyInterface;
use Pim\Component\Catalog\Model\FamilyVariantInterface;
use Pim\Component\Catalog\Model\ProductModelInterface;
use Pim\Component\Enrich\Converter\ConverterInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class ProductModelNormalizerSpec extends ObjectBehavior
{
    function let(
        NormalizerInterface $normalizer,
        NormalizerInterface $versionNormalizer,
        VersionManager $versionManager,
        AttributeConverterInterface $localizedConverter,
        ConverterInterface $productValueConverter,
        FormProviderInterface $formProvider
    ) {
        $this->beConstructedWith(
            $normalizer,
            $versionNormalizer,
            $versionManager,
            $localizedConverter,
            $productValueConverter,
            $formProvider
        );
    }

    function it_supports_product_models(ProductModelInterface $productModel)
    {
        $this->supportsNormalization($productModel, 'internal_api')->shouldReturn(true);
    }

    function it_normalize_products(
        $normalizer,
        $versionNormalizer,
        $versionManager,
        $localizedConverter,
        $productValueConverter,
        $formProvider,
        ProductModelInterface $productModel,
        FamilyVariantInterface $familyVariant,
        FamilyInterface $family
    ) {
        $options = [
            'decimal_separator' => ',',
            'date_format'       => 'dd/MM/yyyy',
        ];

        $productModelNormalized = [
            'identifier'     => 'tshirt_blue',
            'family_variant' => 'tshirts_color',
            'family'         => 'tshirts',
            'categories'     => ['summer'],
            'values'         => [
                'normalized_property' => [['data' => 'a nice normalized property', 'locale' => null, 'scope' => null]],
                'number'              => [['data' => 12.5000, 'locale' => null, 'scope' => null]],
                'metric'              => [['data' => 12.5000, 'locale' => null, 'scope' => null]],
                'prices'              => [['data' => 12.5, 'locale' => null, 'scope' => null]],
                'date'                => [['data' => '2015-01-31', 'locale' => null, 'scope' => null]],
                'picture'             => [['data' => 'a/b/c/my_picture.jpg', 'locale' => null, 'scope' => null]]
            ]
        ];

        $familyVariantNormalized = [
            'code'                   => 'tshirts_color',
            'labels'                 => ['en_US' => 'Tshirts Color', 'fr_FR' => 'Tshirt Couleur'],
            'family'                 => 'tshirts',
            'variant_attribute_sets' => []
        ];

        $valuesLocalized = [
            'normalized_property' => [['data' => 'a nice normalized property', 'locale' => null, 'scope' => null]],
            'number'              => [['data' => '12,5000', 'locale' => null, 'scope' => null]],
            'metric'              => [['data' => '12,5000', 'locale' => null, 'scope' => null]],
            'prices'              => [['data' => '12,5', 'locale' => null, 'scope' => null]],
            'date'                => [['data' => '31/01/2015', 'locale' => null, 'scope' => null]],
            'picture'             => [['data' => 'a/b/c/my_picture.jpg', 'locale' => null, 'scope' => null]]
        ];

        $normalizer->normalize($productModel, 'standard', $options)->willReturn($productModelNormalized);
        $localizedConverter->convertToLocalizedFormats($productModelNormalized['values'], $options)->willReturn($valuesLocalized);

        $valuesConverted = $valuesLocalized;
        $valuesConverted['picture'] = [
            [
                'data' => [
                    'filePath' => 'a/b/c/my_picture.jpg', 'originalFilename' => 'my_picture.jpg'
                ],
                'locale' => null,
                'scope' => null
            ]
        ];

        $productValueConverter->convert($valuesLocalized)->willReturn($valuesConverted);

        $productModel->getId()->willReturn(12);
        $productModel->getIdentifier()->willReturn('tshirt_blue');
        $versionManager->getOldestLogEntry($productModel)->willReturn('create_version');
        $versionNormalizer->normalize('create_version', 'internal_api')->willReturn('normalized_create_version');
        $versionManager->getNewestLogEntry($productModel)->willReturn('update_version');
        $versionNormalizer->normalize('update_version', 'internal_api')->willReturn('normalized_update_version');

        $productModel->getFamilyVariant()->willReturn($familyVariant);
        $familyVariant->getFamily()->willReturn($family);
        $family->getCode()->willReturn('tshirts');
        $normalizer->normalize($familyVariant, 'standard')->willReturn($familyVariantNormalized);

        $formProvider->getForm($productModel)->willReturn('pim-product-model-edit-form');

        $this->normalize($productModel, 'internal_api', $options)->shouldReturn(
            [
                'identifier'     => 'tshirt_blue',
                'family_variant' => 'tshirts_color',
                'family'         => 'tshirts',
                'categories'     => ['summer'],
                'values'         => $valuesConverted,
                'meta'           => [
                    'family_variant' => $familyVariantNormalized,
                    'form'           => 'pim-product-model-edit-form',
                    'id'             => 12,
                    'created'        => 'normalized_create_version',
                    'updated'        => 'normalized_update_version',
                    'model_type'     => 'product_model',
                    'label'          => [
                        'en_US' => 'tshirt_blue'
                    ]
                ]
            ]
        );
    }
}
