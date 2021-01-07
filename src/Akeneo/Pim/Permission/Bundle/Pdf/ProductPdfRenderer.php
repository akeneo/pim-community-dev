<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2014 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Pim\Permission\Bundle\Pdf;

use Akeneo\Channel\Component\Repository\ChannelRepositoryInterface;
use Akeneo\Channel\Component\Repository\LocaleRepositoryInterface;
use Akeneo\Pim\Enrichment\Bundle\PdfGeneration\Builder\PdfBuilderInterface;
use Akeneo\Pim\Enrichment\Bundle\PdfGeneration\Renderer\ProductPdfRenderer as PimProductPdfRenderer;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\Pim\WorkOrganization\Workflow\Bundle\Helper\FilterProductValuesHelper;
use Akeneo\Tool\Component\FileStorage\Model\FileInfoInterface;
use Akeneo\Tool\Component\StorageUtils\Repository\IdentifiableObjectRepositoryInterface;
use Liip\ImagineBundle\Imagine\Cache\CacheManager;
use Liip\ImagineBundle\Imagine\Data\DataManager;
use Liip\ImagineBundle\Imagine\Filter\FilterManager;
use Twig\Environment;

/**
 * PDF renderer used to render PDF for a Product
 *
 * @author Julien Sanchez <julien@akeneo.com>
 */
class ProductPdfRenderer extends PimProductPdfRenderer
{
    private const IMAGE_MIME_TYPE_PREFIX = 'image/';

    protected FilterProductValuesHelper $filterHelper;
    protected ChannelRepositoryInterface $channelRepository;
    protected LocaleRepositoryInterface $localeRepository;

    public function __construct(
        Environment $templating,
        PdfBuilderInterface $pdfBuilder,
        FilterProductValuesHelper $filterHelper,
        DataManager $dataManager,
        CacheManager $cacheManager,
        FilterManager $filterManager,
        IdentifiableObjectRepositoryInterface $attributeRepository,
        ChannelRepositoryInterface $channelRepository,
        LocaleRepositoryInterface $localeRepository,
        string $template,
        IdentifiableObjectRepositoryInterface $attributeOptionRepository,
        ?string $customFont = null
    ) {
        parent::__construct(
            $templating,
            $pdfBuilder,
            $dataManager,
            $cacheManager,
            $filterManager,
            $attributeRepository,
            $template,
            $attributeOptionRepository,
            $customFont
        );

        $this->filterHelper = $filterHelper;
        $this->channelRepository = $channelRepository;
        $this->localeRepository = $localeRepository;
    }

    /**
     * {@inheritdoc}
     */
    protected function getAttributes(ProductInterface $product, $localeCode)
    {
        $values = $this->filterHelper->filter($product->getValues()->toArray(), $localeCode);
        $attributes = [];

        foreach ($values as $value) {
            $attribute = $this->attributeRepository->findOneByIdentifier($value->getAttributeCode());
            if ($attribute !== null) {
                $attributes[$value->getAttributeCode()] = $attribute;
            }
        }

        return $attributes;
    }

    /**
     * Checks a file has a mime type of type image.
     *
     * @param FileInfoInterface $file
     *
     * @return bool
     */
    private function isImage(FileInfoInterface $file): bool
    {
        $fileMimeType = $file->getMimeType();

        return 0 === strpos($fileMimeType, self::IMAGE_MIME_TYPE_PREFIX);
    }
}
