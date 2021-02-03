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
use Akeneo\Pim\Permission\Component\Attributes;
use Akeneo\Pim\Structure\Component\Model\AttributeInterface;
use Akeneo\Pim\WorkOrganization\Workflow\Bundle\Helper\FilterProductValuesHelper;
use Akeneo\Tool\Component\StorageUtils\Repository\IdentifiableObjectRepositoryInterface;
use Liip\ImagineBundle\Imagine\Cache\CacheManager;
use Liip\ImagineBundle\Imagine\Data\DataManager;
use Liip\ImagineBundle\Imagine\Filter\FilterManager;
use Symfony\Bundle\FrameworkBundle\Templating\EngineInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

/**
 * PDF renderer used to render PDF for a Product
 *
 * @author Julien Sanchez <julien@akeneo.com>
 */
class ProductPdfRenderer extends PimProductPdfRenderer
{
    /** @var FilterProductValuesHelper */
    protected $filterHelper;

    /** @var ChannelRepositoryInterface */
    protected $channelRepository;

    /** @var LocaleRepositoryInterface */
    protected $localeRepository;

    /** @var AuthorizationCheckerInterface|null */
    protected $authorizationChecker;

    public function __construct(
        EngineInterface $templating,
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
        ?string $customFont = null,
        ?AuthorizationCheckerInterface $authorizationChecker = null
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
        $this->authorizationChecker = $authorizationChecker;
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
     * Return true if the attribute should be rendered
     */
    protected function canRenderAttribute(?AttributeInterface $attribute): bool
    {
        return parent::canRenderAttribute($attribute) && (
            null === $this->authorizationChecker ||
            $this->authorizationChecker->isGranted(Attributes::VIEW_ATTRIBUTES, $attribute->getGroup())
        );
    }
}
