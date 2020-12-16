<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2019 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Pim\WorkOrganization\Workflow\Bundle\Datagrid\Normalizer;

use Akeneo\Pim\Enrichment\Bundle\Filter\CollectionFilterInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ValueInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\WriteValueCollection;
use Akeneo\Pim\Enrichment\Component\Product\Normalizer\InternalApi\ImageNormalizer;
use Akeneo\Pim\Structure\Component\Model\FamilyTranslationInterface;
use Akeneo\Pim\WorkOrganization\Workflow\Component\Model\PublishedProductInterface;
use Akeneo\Pim\WorkOrganization\Workflow\Component\Query\GetPublishedProductCompletenesses;
use Oro\Bundle\PimDataGridBundle\Normalizer\IdEncoder;
use Symfony\Component\Serializer\Normalizer\CacheableSupportsMethodInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareTrait;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Webmozart\Assert\Assert;

/**
 * @author Mathias METAYER <mathias.metayer@akeneo.com>
 */
class PublishedProductNormalizer implements NormalizerInterface, NormalizerAwareInterface, CacheableSupportsMethodInterface
{
    use NormalizerAwareTrait;

    /** @var ImageNormalizer */
    private $imageNormalizer;

    /** @var CollectionFilterInterface */
    private $filter;

    /** @var GetPublishedProductCompletenesses */
    private $getPublishedProductCompletenesses;

    public function __construct(
        CollectionFilterInterface $filter,
        ImageNormalizer $imageNormalizer,
        GetPublishedProductCompletenesses $getPublishedProductCompletenesses
    ) {
        $this->filter = $filter;
        $this->imageNormalizer = $imageNormalizer;
        $this->getPublishedProductCompletenesses = $getPublishedProductCompletenesses;
    }

    /**
     * {@inheritdoc}
     */
    public function normalize($publishedProduct, $format = null, array $context = []): array
    {
        if (!$this->normalizer instanceof NormalizerInterface) {
            throw new \LogicException('Serializer must be a normalizer');
        }

        $context = array_merge(['filter_types' => ['pim.transform.product_value.structured']], $context);
        $data = [];
        $locale = current($context['locales']);
        $scope = current($context['channels']);

        $data['identifier'] = $publishedProduct->getIdentifier();
        $data['family'] = $this->getFamilyLabel($publishedProduct, $locale);
        $data['groups'] = $this->getGroupsLabels($publishedProduct, $locale);
        $data['enabled'] = (bool)$publishedProduct->isEnabled();
        $data['values'] = $this->normalizeValues($publishedProduct->getValues(), $format, $context);
        $data['created'] = $this->normalizer->normalize($publishedProduct->getCreated(), $format, $context);
        $data['updated'] = $this->normalizer->normalize($publishedProduct->getUpdated(), $format, $context);
        $data['label'] = $publishedProduct->getLabel($locale, $scope);
        $data['image'] = $this->normalizeImage($publishedProduct->getImage(), $context);
        $data['completeness'] = $this->getCompletenessRatio($publishedProduct, $context);
        $data['document_type'] = IdEncoder::PRODUCT_TYPE;
        $data['technical_id'] = $publishedProduct->getId();
        $data['search_id'] = IdEncoder::encode($data['document_type'], $data['technical_id']);
        $data['is_checked'] = false;
        $data['complete_variant_product'] = null;
        $data['parent'] = null;

        return $data;
    }

    /**
     * {@inheritdoc}
     */
    public function supportsNormalization($data, $format = null)
    {
        return $data instanceof PublishedProductInterface && 'datagrid' === $format;
    }

    public function hasCacheableSupportsMethod(): bool
    {
        return true;
    }

    private function getFamilyLabel(ProductInterface $product, $locale): ?string
    {
        $family = $product->getFamily();
        if (null === $family) {
            return null;
        }

        $translation = $family->getTranslation($locale);
        Assert::implementsInterface($translation, FamilyTranslationInterface::class);

        return $this->getLabel($family->getCode(), $translation->getLabel());
    }

    /**
     * @param ProductInterface $product
     * @param string $locale
     *
     * @return string
     */
    protected function getGroupsLabels(ProductInterface $product, $locale)
    {
        $groups = [];
        foreach ($product->getGroups() as $group) {
            $translation = $group->getTranslation($locale);
            $groups[] = $this->getLabel($group->getCode(), $translation->getLabel());
        }

        return implode(', ', $groups);
    }

    private function getCompletenessRatio(PublishedProductInterface $product, array $context): ?int
    {
        $completenesses = $this->getPublishedProductCompletenesses->fromPublishedProductId($product->getId());
        $channel = current($context['channels']);
        $locale = current($context['locales']);
        $completeness = $completenesses->getCompletenessForChannelAndLocale($channel, $locale);

        return $completeness ? $completeness->ratio() : null;
    }

    private function getLabel($code, $value = null)
    {
        return '' === $value || null === $value ? sprintf('[%s]', $code) : $value;
    }

    private function normalizeImage(?ValueInterface $data, array $context = []): ?array
    {
        return $this->imageNormalizer->normalize(
            $data,
            $context['data_locale'] ?? null,
            $context['data_channel'] ?? null
        );
    }

    private function normalizeValues(WriteValueCollection $values, $format, array $context = [])
    {
        foreach ($context['filter_types'] as $filterType) {
            $values = $this->filter->filterCollection($values, $filterType, $context);
        }

        $data = $this->normalizer->normalize($values, $format, $context);

        return $data;
    }
}
