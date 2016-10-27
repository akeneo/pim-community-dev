<?php

namespace Pim\Bundle\EnrichBundle\Normalizer;

use Akeneo\Component\FileStorage\Repository\FileInfoRepositoryInterface;
use Pim\Bundle\EnrichBundle\Provider\Form\FormProviderInterface;
use Pim\Bundle\EnrichBundle\Provider\StructureVersion\StructureVersionProviderInterface;
use Pim\Bundle\VersioningBundle\Manager\VersionManager;
use Pim\Component\Catalog\Localization\Localizer\AttributeConverterInterface;
use Pim\Component\Catalog\Model\ProductInterface;
use Pim\Component\Catalog\Repository\AttributeRepositoryInterface;
use Pim\Component\Catalog\Repository\LocaleRepositoryInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

/**
 * Product normalizer
 *
 * @author    Julien Sanchez <julien@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ProductNormalizer implements NormalizerInterface
{
    /** @var string[] */
    protected $supportedFormat = ['internal_api'];

    /** @var NormalizerInterface */
    protected $productNormalizer;

    /** @var NormalizerInterface */
    protected $versionNormalizer;

    /** @var VersionManager */
    protected $versionManager;

    /** @var LocaleRepositoryInterface */
    protected $localeRepository;

    /** @var StructureVersionProviderInterface */
    protected $structureVersionProvider;

    /** @var FormProviderInterface */
    protected $formProvider;

    /** @var AttributeConverterInterface */
    protected $localizedConverter;

    /** @var AttributeRepositoryInterface */
    protected $attributeRepository;

    /** @var FileInfoRepositoryInterface */
    protected $fileInfoRepository;

    /**
     * @param NormalizerInterface               $productNormalizer
     * @param NormalizerInterface               $versionNormalizer
     * @param VersionManager                    $versionManager
     * @param LocaleRepositoryInterface         $localeRepository
     * @param StructureVersionProviderInterface $structureVersionProvider
     * @param FormProviderInterface             $formProvider
     * @param AttributeConverterInterface       $localizedConverter
     * @param AttributeRepositoryInterface      $attributeRepository
     * @param FileInfoRepositoryInterface       $fileInfoRepository
     */
    public function __construct(
        NormalizerInterface $productNormalizer,
        NormalizerInterface $versionNormalizer,
        VersionManager $versionManager,
        LocaleRepositoryInterface $localeRepository,
        StructureVersionProviderInterface $structureVersionProvider,
        FormProviderInterface $formProvider,
        AttributeConverterInterface $localizedConverter,
        AttributeRepositoryInterface $attributeRepository,
        FileInfoRepositoryInterface $fileInfoRepository
    ) {
        $this->productNormalizer = $productNormalizer;
        $this->versionNormalizer = $versionNormalizer;
        $this->versionManager = $versionManager;
        $this->localeRepository = $localeRepository;
        $this->structureVersionProvider = $structureVersionProvider;
        $this->formProvider = $formProvider;
        $this->localizedConverter = $localizedConverter;
        $this->attributeRepository = $attributeRepository;
        $this->fileInfoRepository = $fileInfoRepository;
    }

    /**
     * {@inheritdoc}
     */
    public function normalize($product, $format = null, array $context = [])
    {
        $normalizedProduct = $this->productNormalizer->normalize($product, 'standard', $context);
        $normalizedProduct['values'] = $this->localizedConverter->convertToLocalizedFormats(
            $normalizedProduct['values'],
            $context
        );

        $normalizedProduct['values'] = $this->convertMedia($normalizedProduct['values']);

        $oldestLog = $this->versionManager->getOldestLogEntry($product);
        $newestLog = $this->versionManager->getNewestLogEntry($product);

        $created = null !== $oldestLog ? $this->versionNormalizer->normalize($oldestLog, 'internal_api') : null;
        $updated = null !== $newestLog ? $this->versionNormalizer->normalize($newestLog, 'internal_api') : null;

        $normalizedProduct['meta'] = [
            'form'              => $this->formProvider->getForm($product),
            'id'                => $product->getId(),
            'created'           => $created,
            'updated'           => $updated,
            'model_type'        => 'product',
            'structure_version' => $this->structureVersionProvider->getStructureVersion(),
        ] + $this->getLabels($product) + $this->getAssociationMeta($product);

        return $normalizedProduct;
    }

    /**
     * {@inheritdoc}
     */
    public function supportsNormalization($data, $format = null)
    {
        return $data instanceof ProductInterface && in_array($format, $this->supportedFormat);
    }

    /**
     * @param ProductInterface $product
     *
     * @return array
     */
    protected function getLabels(ProductInterface $product)
    {
        $labels = [];

        foreach ($this->localeRepository->getActivatedLocaleCodes() as $localeCode) {
            $labels[$localeCode] = $product->getLabel($localeCode);
        }

        return ['label' => $labels];
    }

    /**
     * @param ProductInterface $product
     *
     * @return array
     */
    protected function getAssociationMeta(ProductInterface $product)
    {
        $meta = [];
        $associations = $product->getAssociations();

        foreach ($associations as $association) {
            $associationType = $association->getAssociationType();
            $meta[$associationType->getCode()]['groupIds'] = array_map(
                function ($group) {
                    return $group->getId();
                },
                $association->getGroups()->toArray()
            );
        }

        return ['associations' => $meta];
    }

    /**
     * Convert media attributes to have "originalFilename" in addition to "filePath"
     * Before:
     * {
     *     "picture": {
     *          "locale": null,
     *          "scope": null,
     *          "data": "a/b/c/b/s936265s65_my_picture.jpg"
     *      }
     * }
     *
     * After:
     * {
     *    "picture": {
     *         "locale": null,
     *         "scope": null,
     *         "data": {
     *             "originalFilename": "my_picture.jpg",
     *             "filePath": "a/b/c/b/s936265s65_my_picture.jpg"
     *         }
     *     }
     * }
     *
     * @param array $normalizedProduct
     *
     * @return array
     */
    protected function convertMedia(array $normalizedProduct)
    {
        $mediaAttributes = $this->attributeRepository->findMediaAttributeCodes();

        foreach ($normalizedProduct as $code => $values) {
            if (in_array($code, $mediaAttributes)) {
                foreach ($values as $index => $value) {
                    $file = $this->fileInfoRepository->findOneByIdentifier($value['data']);
                    $data = [
                        'filePath'         => $value['data'],
                        'originalFilename' => null,
                    ];

                    if (null !== $file) {
                        $data['originalFilename'] = $file->getOriginalFilename();
                    }

                    $normalizedProduct[$code][$index]['data'] = $data;
                }
            }
        }

        return $normalizedProduct;
    }
}
