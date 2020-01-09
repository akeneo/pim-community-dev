<?php

namespace Akeneo\Pim\Enrichment\AssetManager\Component\Normalizer\InternalApi;

use Akeneo\AssetManager\Infrastructure\PublicApi\Enrich\AssetPreviewGenerator;
use Akeneo\Channel\Component\Repository\LocaleRepositoryInterface;
use Akeneo\Pim\Enrichment\AssetManager\Component\Value\AssetCollectionValue;
use Akeneo\Pim\Enrichment\Component\Product\Model\ValueInterface;
use Akeneo\Pim\Enrichment\Component\Product\Normalizer\InternalApi\FileNormalizer;
use Akeneo\Pim\Enrichment\Component\Product\Normalizer\InternalApi\ImageNormalizer as BaseImageNormalizer;
use Akeneo\Pim\Enrichment\Component\Product\Repository\ReferenceDataRepositoryResolverInterface;
use Akeneo\Pim\Enrichment\Component\Product\Value\ReferenceDataCollectionValue;
use Akeneo\Tool\Component\StorageUtils\Repository\IdentifiableObjectRepositoryInterface;

/**
 * Image Normalizer for Enterprise Edition.
 *
 * This Image Normalizer is able to normalize images coming from Asset attributes, to display the first asset of a
 * collection.
 *
 * @author Pierre Allard <pierre.allard@akeneo.com>
 */
class ImageNormalizer extends BaseImageNormalizer
{
    /** @var IdentifiableObjectRepositoryInterface */
    protected $attributeRepository;

    /** @var AssetPreviewGenerator */
    private $assetPreviewGenerator;

    public function __construct(
        FileNormalizer $fileNormalizer,
        IdentifiableObjectRepositoryInterface $attributeRepository,
        AssetPreviewGenerator $assetPreviewGenerator
    ) {
        parent::__construct($fileNormalizer);

        $this->attributeRepository = $attributeRepository;
        $this->assetPreviewGenerator = $assetPreviewGenerator;
    }

    /**
     * {@inheritdoc}
     */
    public function normalize(?ValueInterface $value, ?string $localeCode = null): ?array
    {
      if (null === $value) {
        return null;
      }
      $data = $value->getData();

      if (empty($data)) {
          return null;
      }

      $attribute = $this->attributeRepository->findOneByIdentifier($value->getAttributeCode());
      $assetFamilyIdentifier = $attribute->getReferenceDataName();

      $assetCode = $data[0];
      $filepath = $this->assetPreviewGenerator->getImageUrl(
          (string) $assetCode,
          $assetFamilyIdentifier,
          $value->getScopeCode(),
          $value->getLocaleCode(),
          'thumbnail'
      );

      return [
          'filePath' => $filepath,
          'originalFilename' => '',
      ];
    }
}
