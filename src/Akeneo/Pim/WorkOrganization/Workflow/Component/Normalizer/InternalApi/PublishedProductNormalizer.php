<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2015 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Pim\WorkOrganization\Workflow\Component\Normalizer\InternalApi;

use Akeneo\Channel\Infrastructure\Component\Repository\LocaleRepositoryInterface;
use Akeneo\Pim\Enrichment\Component\Product\Association\MissingAssociationAdder;
use Akeneo\Pim\Enrichment\Component\Product\Converter\ConverterInterface;
use Akeneo\Pim\Enrichment\Component\Product\Localization\Localizer\AttributeConverterInterface;
use Akeneo\Pim\Enrichment\Component\Product\Normalizer\InternalApi\ImageNormalizer;
use Akeneo\Pim\Enrichment\Component\Product\Normalizer\InternalApi\ProductNormalizer;
use Akeneo\Pim\Enrichment\Component\Product\ValuesFiller\FillMissingValuesInterface;
use Akeneo\Pim\Permission\Bundle\Entity\Repository\CategoryAccessRepository;
use Akeneo\Pim\Permission\Component\Attributes;
use Akeneo\Pim\WorkOrganization\Workflow\Bundle\Normalizer\PublishedProductNormalizer as StandardPublishedProductNormalizer;
use Akeneo\Pim\WorkOrganization\Workflow\Component\Model\PublishedProductInterface;
use Akeneo\Pim\WorkOrganization\Workflow\Component\Query\GetPublishedProductCompletenesses;
use Akeneo\Platform\Bundle\UIBundle\Provider\Form\FormProviderInterface;
use Akeneo\Platform\Bundle\UIBundle\Provider\StructureVersion\StructureVersionProviderInterface;
use Akeneo\Tool\Bundle\VersioningBundle\Manager\VersionManager;
use Akeneo\Tool\Component\Versioning\Model\VersionInterface;
use Akeneo\UserManagement\Bundle\Context\UserContext;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Serializer\Normalizer\CacheableSupportsMethodInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

/**
 * Published product normalizer
 *
 * @author Julien Sanchez <julien@akeneo.com>
 */
class PublishedProductNormalizer implements NormalizerInterface, CacheableSupportsMethodInterface
{
    /** @var StandardPublishedProductNormalizer */
    private $standardPublishedProductNormalizer;

    /** @var MissingAssociationAdder */
    private $missingAssociationAdder;

    /** @var FillMissingValuesInterface */
    private $fillMissingPublishedProductValues;

    /** @var AttributeConverterInterface */
    private $localizedConverter;

    /** @var ConverterInterface */
    private $productValueConverter;

    /** @var NormalizerInterface */
    private $versionNormalizer;

    /** @var VersionManager */
    private $versionManager;

    /** @var UserContext */
    private $userContext;

    /** @var FormProviderInterface */
    private $formProvider;

    /** @var StructureVersionProviderInterface */
    private $structureVersionProvider;

    /** @var GetPublishedProductCompletenesses */
    private $getPublishedProductCompletenesses;

    /** @var ImageNormalizer */
    private $imageNormalizer;

    /** @var LocaleRepositoryInterface */
    private $localeRepository;

    /** @var ProductNormalizer */
    private $internalApiProductNormalizer;

    /** @var AuthorizationCheckerInterface */
    private $authorizationChecker;

    /** @var CategoryAccessRepository */
    private $categoryAccessRepo;

    /** @var NormalizerInterface */
    private $completenessCollectionNormalizer;

    public function __construct(
        StandardPublishedProductNormalizer $standardPublishedProductNormalizer,
        MissingAssociationAdder $missingAssociationAdder,
        FillMissingValuesInterface $fillMissingPublishedProductValues,
        AttributeConverterInterface $localizedConverter,
        ConverterInterface $productValueConverter,
        NormalizerInterface $versionNormalizer,
        VersionManager $versionManager,
        UserContext $userContext,
        FormProviderInterface $formProvider,
        StructureVersionProviderInterface $structureVersionProvider,
        GetPublishedProductCompletenesses $getPublishedProductCompletenesses,
        ImageNormalizer $imageNormalizer,
        LocaleRepositoryInterface $localeRepository,
        ProductNormalizer $internalApiProductNormalizer,
        AuthorizationCheckerInterface $authorizationChecker,
        CategoryAccessRepository $categoryAccessRepo,
        NormalizerInterface $completenessCollectionNormalizer
    ) {
        $this->standardPublishedProductNormalizer = $standardPublishedProductNormalizer;
        $this->missingAssociationAdder = $missingAssociationAdder;
        $this->fillMissingPublishedProductValues = $fillMissingPublishedProductValues;
        $this->localizedConverter = $localizedConverter;
        $this->productValueConverter = $productValueConverter;
        $this->versionNormalizer = $versionNormalizer;
        $this->versionManager = $versionManager;
        $this->userContext = $userContext;
        $this->formProvider = $formProvider;
        $this->structureVersionProvider = $structureVersionProvider;
        $this->getPublishedProductCompletenesses = $getPublishedProductCompletenesses;
        $this->imageNormalizer = $imageNormalizer;
        $this->localeRepository = $localeRepository;
        $this->internalApiProductNormalizer = $internalApiProductNormalizer;
        $this->authorizationChecker = $authorizationChecker;
        $this->categoryAccessRepo = $categoryAccessRepo;
        $this->completenessCollectionNormalizer = $completenessCollectionNormalizer;
    }

    /**
     * {@inheritdoc}
     */
    public function normalize($publishedProduct, $format = null, array $context = [])
    {
        $this->missingAssociationAdder->addMissingAssociations($publishedProduct);
        $normalizedPublishedProduct = $this->standardPublishedProductNormalizer->normalize(
            $publishedProduct,
            'standard',
            $context
        );

        $normalizedPublishedProduct = $this->fillMissingPublishedProductValues->fromStandardFormat(
            $normalizedPublishedProduct
        );

        $normalizedPublishedProduct['values'] = $this->productValueConverter->convert(
            $this->localizedConverter->convertToLocalizedFormats($normalizedPublishedProduct['values'], $context)
        );

        $userTimezone = $this->userContext->getUserTimezone();

        $ownerGroups = $this->categoryAccessRepo->getGrantedUserGroupsForEntityWithValues($publishedProduct, Attributes::OWN_PRODUCTS);

        $normalizedPublishedProduct['parent_associations'] = [];

        $normalizedPublishedProduct['meta'] = [
            'form' => $this->formProvider->getForm($publishedProduct),
            'id' => $publishedProduct->getId(),
            'created' => $this->normalizeVersion($this->versionManager->getOldestLogEntry($publishedProduct), $userTimezone),
            'updated' => $this->normalizeVersion($this->versionManager->getNewestLogEntry($publishedProduct), $userTimezone),
            'model_type' => 'product',
            'structure_version' => $this->structureVersionProvider->getStructureVersion(),
            'completenesses' => $this->getNormalizedCompletenesses($publishedProduct),
            'image' => $this->imageNormalizer->normalize($publishedProduct->getImage()),
            'ascendant_category_ids' => [],
            'variant_navigation' => [],
            'attributes_for_this_level' => [],
            'attributes_axes' => [],
            'parent_attributes' => [],
            'family_variant' => null,
            'level' => null,
            'label' => $this->getLabels($publishedProduct, $context['channel'] ?? null),
            'associations' => $this->getAssociationMeta($publishedProduct),
            'published' => null,
            'owner_groups' => $ownerGroups,
            'is_owner' => $this->authorizationChecker->isGranted(Attributes::OWN, $publishedProduct),
            'working_copy' => $this->internalApiProductNormalizer->normalize($publishedProduct->getOriginalProduct(), 'standard', $context),
            'draft_status' => null,
            'original_product_uuid' => $publishedProduct->getOriginalProduct()->getUuid()->toString(),
        ];

        return $normalizedPublishedProduct;
    }

    /**
     * {@inheritdoc}
     */
    public function supportsNormalization($data, $format = null): bool
    {
        return $data instanceof PublishedProductInterface && 'internal_api' === $format;
    }

    public function hasCacheableSupportsMethod(): bool
    {
        return true;
    }

    private function getLabels(PublishedProductInterface $publishedProduct, ?string $scopeCode): array
    {
        $labels = [];

        foreach ($this->localeRepository->getActivatedLocaleCodes() as $localeCode) {
            $labels[$localeCode] = $publishedProduct->getLabel($localeCode, $scopeCode);
        }

        return $labels;
    }

    private function getAssociationMeta(PublishedProductInterface $publishedProduct): array
    {
        $associations = [];

        foreach ($publishedProduct->getAssociations() as $association) {
            $associations[$association->getAssociationType()->getCode()]['groupIds'] = $association->getGroups()->map(
                function ($group) {
                    return $group->getId();
                }
            )->toArray();
        }

        return $associations;
    }

    private function normalizeVersion(?VersionInterface $version, string $userTimezone): ?array
    {
        if (null === $version) {
            return null;
        }

        return $this->versionNormalizer->normalize($version, 'internal_api', ['timezone' => $userTimezone]);
    }

    private function getNormalizedCompletenesses(PublishedProductInterface $publishedProduct): array
    {
        $completenessCollection = $this->getPublishedProductCompletenesses->fromPublishedProductId($publishedProduct->getId());

        return $this->completenessCollectionNormalizer->normalize($completenessCollection, 'internal_api');
    }
}
