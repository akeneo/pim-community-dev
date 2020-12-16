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

namespace Specification\Akeneo\Pim\WorkOrganization\Workflow\Component\Normalizer\InternalApi;

use Akeneo\Channel\Component\Repository\LocaleRepositoryInterface;
use Akeneo\Pim\Enrichment\Component\Product\Association\MissingAssociationAdder;
use Akeneo\Pim\Enrichment\Component\Product\Converter\ConverterInterface;
use Akeneo\Pim\Enrichment\Component\Product\Localization\Localizer\AttributeConverterInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\Product;
use Akeneo\Pim\Enrichment\Component\Product\Normalizer\InternalApi\ImageNormalizer;
use Akeneo\Pim\Enrichment\Component\Product\Normalizer\InternalApi\ProductNormalizer;
use Akeneo\Pim\Enrichment\Component\Product\Normalizer\InternalApi\VersionNormalizer;
use Akeneo\Pim\Enrichment\Component\Product\Value\ScalarValue;
use Akeneo\Pim\Permission\Bundle\Entity\Repository\CategoryAccessRepository;
use Akeneo\Pim\Permission\Component\Attributes;
use Akeneo\Pim\WorkOrganization\Workflow\Bundle\Normalizer\PublishedProductNormalizer as StandardPublishedProductNormalizer;
use Akeneo\Pim\WorkOrganization\Workflow\Component\Model\Projection\PublishedProductCompleteness;
use Akeneo\Pim\WorkOrganization\Workflow\Component\Model\Projection\PublishedProductCompletenessCollection;
use Akeneo\Pim\WorkOrganization\Workflow\Component\Model\PublishedProduct;
use Akeneo\Pim\WorkOrganization\Workflow\Component\Normalizer\InternalApi\FillMissingPublishedProductValues;
use Akeneo\Pim\WorkOrganization\Workflow\Component\Normalizer\InternalApi\PublishedProductNormalizer;
use Akeneo\Pim\WorkOrganization\Workflow\Component\Query\GetPublishedProductCompletenesses;
use Akeneo\Platform\Bundle\UIBundle\Provider\Form\FormProviderInterface;
use Akeneo\Platform\Bundle\UIBundle\Provider\StructureVersion\StructureVersionProviderInterface;
use Akeneo\Tool\Bundle\VersioningBundle\Manager\VersionManager;
use Akeneo\Tool\Component\Versioning\Model\VersionInterface;
use Akeneo\UserManagement\Bundle\Context\UserContext;
use Doctrine\Common\Collections\ArrayCollection;
use PhpSpec\ObjectBehavior;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

final class PublishedProductNormalizerSpec extends ObjectBehavior
{
    public function let(
        StandardPublishedProductNormalizer $standardPublishedProductNormalizer,
        MissingAssociationAdder $missingAssociationAdder,
        FillMissingPublishedProductValues $productValuesFiller,
        AttributeConverterInterface $localizedConverter,
        ConverterInterface $productValueConverter,
        UserContext $userContext,
        CategoryAccessRepository $categoryAccessRepository,
        VersionManager $versionManager,
        FormProviderInterface $formProvider,
        VersionNormalizer $versionNormalizer,
        StructureVersionProviderInterface $structureVersionProvider,
        GetPublishedProductCompletenesses $getPublishedProductCompletenesses,
        ImageNormalizer $imageNormalizer,
        LocaleRepositoryInterface $localeRepository,
        ProductNormalizer $internalApiProductNormalizer,
        AuthorizationCheckerInterface $authorizationChecker,
        NormalizerInterface $completenessCollectionNormalizer
    ) {
        $this->beConstructedWith(
            $standardPublishedProductNormalizer,
            $missingAssociationAdder,
            $productValuesFiller,
            $localizedConverter,
            $productValueConverter,
            $versionNormalizer,
            $versionManager,
            $userContext,
            $formProvider,
            $structureVersionProvider,
            $getPublishedProductCompletenesses,
            $imageNormalizer,
            $localeRepository,
            $internalApiProductNormalizer,
            $authorizationChecker,
            $categoryAccessRepository,
            $completenessCollectionNormalizer
        );
    }

    public function it_is_initializable()
    {
        $this->shouldBeAnInstanceOf(PublishedProductNormalizer::class);
    }

    public function it_normalizes_a_published_product(
        StandardPublishedProductNormalizer $standardPublishedProductNormalizer,
        MissingAssociationAdder $missingAssociationAdder,
        FillMissingPublishedProductValues $productValuesFiller,
        AttributeConverterInterface $localizedConverter,
        ConverterInterface $productValueConverter,
        UserContext $userContext,
        CategoryAccessRepository $categoryAccessRepository,
        FormProviderInterface $formProvider,
        VersionInterface $oldestVersion,
        VersionInterface $newestVersion,
        VersionManager $versionManager,
        VersionNormalizer $versionNormalizer,
        StructureVersionProviderInterface $structureVersionProvider,
        GetPublishedProductCompletenesses $getPublishedProductCompletenesses,
        ImageNormalizer $imageNormalizer,
        LocaleRepositoryInterface $localeRepository,
        ProductNormalizer $internalApiProductNormalizer,
        AuthorizationCheckerInterface $authorizationChecker,
        NormalizerInterface $completenessCollectionNormalizer
    ) {
        $product = new Product();
        $product->setId(62);

        $publishedProduct = new PublishedProduct();
        $publishedProduct->setId(42);
        $publishedProduct->setOriginalProduct($product);
        $publishedProduct->setFamily(null);
        $publishedProduct->addValue(ScalarValue::value('identifier', 'an_identifier'));
        $publishedProduct->setIdentifier('an_identifier');

        $missingAssociationAdder->addMissingAssociations($publishedProduct)->shouldBeCalled();

        $standard = [
            'identifier' => 'my_identifier',
            'label' => 'My product',
            'family' => 'familyA',
            'parent' => 'product_model_1',
            'groups' => [],
            'categories' => [],
            'enabled' => true,
            'values' => [],
            'created' => '2019-05-14',
            'updated' => '2019-05-15',
            'associations' => [
                'UPSELL' => [
                    'products' => [],
                    'product_models' => [],
                    'groups' => [],
                ],
            ],
        ];
        $standardFilled = $standard;
        $standardPublishedProductNormalizer->normalize($publishedProduct, 'standard', [])->willReturn($standard);
        $productValuesFiller->fromStandardFormat($standard)->willReturn($standardFilled);

        $userTimezone = 'Pacific/Kiritimati';

        $localizedConverter->convertToLocalizedFormats([], [])->willReturn([]);
        $productValueConverter->convert([])->willReturn([]);
        $userContext->getUserTimezone()->willReturn($userTimezone);
        $categoryAccessRepository->getGrantedUserGroupsForEntityWithValues($publishedProduct, Attributes::OWN_PRODUCTS)->willReturn([]);

        $formProvider->getForm($publishedProduct)->willReturn('pim-product-edit-form');

        $versionManager->getOldestLogEntry($publishedProduct)->willReturn($oldestVersion);
        $versionManager->getNewestLogEntry($publishedProduct)->willReturn($newestVersion);
        $versionNormalizer->normalize($oldestVersion, 'internal_api', ['timezone' => $userTimezone])->willReturn([]);
        $versionNormalizer->normalize($newestVersion, 'internal_api', ['timezone' => $userTimezone])->willReturn([]);

        $structureVersionProvider->getStructureVersion()->willReturn(12456);

        $completeness = new PublishedProductCompleteness('ecommerce', 'en_US', 1, []);
        $completenessCollection = new PublishedProductCompletenessCollection(42, [$completeness]);
        $normalizedCompletenesses = [
            [
                'channel' => 'ecommerce',
                'labels' => [
                    'en_US' => 'Ecommerce',
                ],
                'locales' => [
                    'en_US' => [
                        'completeness' => [
                            'required' => 1,
                            'missing' => 0,
                            'ratio' => 100,
                            'locale' => 'en_US',
                            'channel' => 'ecommerce',
                        ],
                        'missing' => [],
                        'label' => 'English (United States)',
                    ],
                ],
                'stats' => [
                    'total' => 1,
                    'complete' => 1,
                    'average' => 100,
                ],
            ],
        ];
        $completenessCollectionNormalizer->normalize($completenessCollection, 'internal_api')->willReturn(
            $normalizedCompletenesses
        );

        $getPublishedProductCompletenesses->fromPublishedProductId(42)->willReturn($completenessCollection);
        $imageNormalizer->normalize(null)->willReturn(null);
        $localeRepository->getActivatedLocaleCodes()->willReturn(['en_US']);
        $internalApiProductNormalizer->normalize($product, 'standard', [])->willReturn([]);
        $authorizationChecker->isGranted(Attributes::OWN, $publishedProduct)->willReturn(false);
        $this->normalize($publishedProduct, 'internal_api', [])->shouldReturn(
            [
                'identifier' => 'my_identifier',
                'label' => 'My product',
                'family' => 'familyA',
                'parent' => 'product_model_1',
                'groups' => [],
                'categories' => [],
                'enabled' => true,
                'values' => [],
                'created' => '2019-05-14',
                'updated' => '2019-05-15',
                'associations' => [
                    'UPSELL' => [
                        'products' => [],
                        'product_models' => [],
                        'groups' => [],
                    ],
                ],
                'parent_associations' => [],
                'meta' => [
                    'form' => 'pim-product-edit-form',
                    'id' => 42,
                    'created' => [],
                    'updated' => [],
                    'model_type' => 'product',
                    'structure_version' => 12456,
                    'completenesses' => $normalizedCompletenesses,
                    'image' => null,
                    'ascendant_category_ids' => [],
                    'variant_navigation' => [],
                    'attributes_for_this_level' => [],
                    'attributes_axes' => [],
                    'parent_attributes' => [],
                    'family_variant' => null,
                    'level' => null,
                    'label' => ['en_US' => 'an_identifier'],
                    'associations' => [],
                    'published' => null,
                    'owner_groups' => [],
                    'is_owner' => false,
                    'working_copy' => [],
                    'draft_status' => null,
                    'original_product_id' => 62,
                ]
            ]
        );
    }
}
