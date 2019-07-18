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

use Akeneo\Asset\Component\Normalizer\InternalApi\ImageNormalizer;
use Akeneo\Channel\Component\Repository\LocaleRepositoryInterface;
use Akeneo\Pim\Enrichment\Component\Product\Association\MissingAssociationAdder;
use Akeneo\Pim\Enrichment\Component\Product\Completeness\CompletenessCalculatorInterface;
use Akeneo\Pim\Enrichment\Component\Product\Converter\ConverterInterface;
use Akeneo\Pim\Enrichment\Component\Product\Localization\Localizer\AttributeConverterInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\CompletenessInterface;
use Akeneo\Pim\Enrichment\Component\Product\Normalizer\InternalApi\FileNormalizer;
use Akeneo\Pim\Enrichment\Component\Product\Normalizer\InternalApi\ProductNormalizer;
use Akeneo\Pim\Enrichment\Component\Product\ValuesFiller\EntityWithFamilyValuesFillerInterface;
use Akeneo\Pim\Permission\Bundle\Entity\Repository\CategoryAccessRepository;
use Akeneo\Pim\Permission\Component\Attributes;
use Akeneo\Pim\Structure\Component\Model\AttributeInterface;
use Akeneo\Pim\WorkOrganization\Workflow\Bundle\Normalizer\PublishedProductNormalizer as StandardPublishedProductNormalizer;
use Akeneo\Pim\WorkOrganization\Workflow\Component\Model\Projection\PublishedProductCompleteness;
use Akeneo\Pim\WorkOrganization\Workflow\Component\Model\Projection\PublishedProductCompletenessCollection;
use Akeneo\Pim\WorkOrganization\Workflow\Component\Model\PublishedProductInterface;
use Akeneo\Pim\WorkOrganization\Workflow\Component\Query\GetPublishedProductCompletenesses;
use Akeneo\Platform\Bundle\UIBundle\Provider\Form\FormProviderInterface;
use Akeneo\Platform\Bundle\UIBundle\Provider\StructureVersion\StructureVersionProviderInterface;
use Akeneo\Tool\Bundle\VersioningBundle\Manager\VersionManager;
use Akeneo\Tool\Component\Versioning\Model\VersionInterface;
use Akeneo\UserManagement\Bundle\Context\UserContext;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

/**
 * Published product normalizer
 *
 * @author Julien Sanchez <julien@akeneo.com>
 */
class PublishedProductNormalizer implements NormalizerInterface
{
    /** @var StandardPublishedProductNormalizer */
    private $standardPublishedProductNormalizer;

    /** @var MissingAssociationAdder */
    private $missingAssociationAdder;

    /** @var EntityWithFamilyValuesFillerInterface */
    private $productValuesFiller;

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

    /** @var CompletenessCalculatorInterface */
    private $completenessCalculator;

    /** @var NormalizerInterface */
    private $incompleteValuesNormalizer;

    /** @var FileNormalizer */
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
        EntityWithFamilyValuesFillerInterface $productValuesFiller,
        AttributeConverterInterface $localizedConverter,
        ConverterInterface $productValueConverter,
        NormalizerInterface $versionNormalizer,
        VersionManager $versionManager,
        UserContext $userContext,
        FormProviderInterface $formProvider,
        StructureVersionProviderInterface $structureVersionProvider,
        GetPublishedProductCompletenesses $getPublishedProductCompletenesses,
        CompletenessCalculatorInterface $completenessCalculator,
        NormalizerInterface $incompleteValuesNormalizer,
        ImageNormalizer $imageNormalizer,
        LocaleRepositoryInterface $localeRepository,
        ProductNormalizer $internalApiProductNormalizer,
        AuthorizationCheckerInterface $authorizationChecker,
        CategoryAccessRepository $categoryAccessRepo,
        NormalizerInterface $completenessCollectionNormalizer
    ) {
        $this->standardPublishedProductNormalizer = $standardPublishedProductNormalizer;
        $this->missingAssociationAdder = $missingAssociationAdder;
        $this->productValuesFiller = $productValuesFiller;
        $this->localizedConverter = $localizedConverter;
        $this->productValueConverter = $productValueConverter;
        $this->versionNormalizer = $versionNormalizer;
        $this->versionManager = $versionManager;
        $this->userContext = $userContext;
        $this->formProvider = $formProvider;
        $this->structureVersionProvider = $structureVersionProvider;
        $this->getPublishedProductCompletenesses = $getPublishedProductCompletenesses;
        $this->completenessCalculator = $completenessCalculator;
        $this->incompleteValuesNormalizer = $incompleteValuesNormalizer;
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
        $this->productValuesFiller->fillMissingValues($publishedProduct);

        $normalizedProduct = $this->standardPublishedProductNormalizer->normalize($publishedProduct, 'standard', $context);

        $normalizedProduct['values'] = $this->productValueConverter->convert(
            $this->localizedConverter->convertToLocalizedFormats($normalizedProduct['values'], $context)
        );

        $userTimezone = $this->userContext->getUserTimezone();

        $ownerGroups = $this->categoryAccessRepo->getGrantedUserGroupsForEntityWithValues($publishedProduct, Attributes::OWN_PRODUCTS);

        $normalizedProduct['parent_associations'] = [];

        $normalizedProduct['meta'] = [
            'form' => $this->formProvider->getForm($publishedProduct),
            'id' => $publishedProduct->getId(),
            'created' => $this->normalizeVersion($this->versionManager->getOldestLogEntry($publishedProduct), $userTimezone),
            'updated' => $this->normalizeVersion($this->versionManager->getNewestLogEntry($publishedProduct), $userTimezone),
            'model_type' => 'product',
            'structure_version' => $this->structureVersionProvider->getStructureVersion(),
            'completenesses' => $this->getNormalizedCompletenesses($publishedProduct),
            'required_missing_attributes' => $this->incompleteValuesNormalizer->normalize($publishedProduct),
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
            'original_product_id' => $publishedProduct->getOriginalProduct()->getId(),
        ];

        return $normalizedProduct;
    }

    /**
     * {@inheritdoc}
     */
    public function supportsNormalization($data, $format = null)
    {
        return $data instanceof PublishedProductInterface && 'internal_api' === $format;
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
        if ($completenessCollection->isEmpty()) {
            $newCompletenesses = $this->completenessCalculator->calculate($publishedProduct);
            $completenessCollection = new PublishedProductCompletenessCollection(
                $publishedProduct->getId(),
                array_map(function (CompletenessInterface $completeness) {
                    return new PublishedProductCompleteness(
                        $completeness->getChannel()->getCode(),
                        $completeness->getLocale()->getCode(),
                        $completeness->getRequiredCount(),
                        $completeness->getMissingAttributes()->map(function (AttributeInterface $attribute) {
                            return $attribute->getCode();
                        })->toArray()
                    );
                }, $newCompletenesses)
            );
        }

        return $this->completenessCollectionNormalizer->normalize($completenessCollection, 'internal_api');
    }
}
