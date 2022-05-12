<?php

namespace Specification\Akeneo\Pim\WorkOrganization\Workflow\Bundle\Normalizer;

use Akeneo\Channel\Infrastructure\Component\Model\Locale;
use Akeneo\Channel\Infrastructure\Component\Model\LocaleInterface;
use Akeneo\Pim\Enrichment\Component\Product\Completeness\Model\ProductCompletenessWithMissingAttributeCodes;
use Akeneo\Pim\Enrichment\Component\Product\Completeness\Model\ProductCompletenessWithMissingAttributeCodesCollection;
use Akeneo\Pim\Enrichment\Component\Product\Normalizer\InternalApi\MissingRequiredAttributesNormalizerInterface;
use Akeneo\Pim\Permission\Component\Attributes;
use Akeneo\Pim\Structure\Component\Model\Attribute;
use Akeneo\Pim\Structure\Component\Model\AttributeGroup;
use Akeneo\Tool\Component\StorageUtils\Repository\IdentifiableObjectRepositoryInterface;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

class MissingRequiredAttributesNormalizerSpec extends ObjectBehavior
{
    function let(
        AuthorizationCheckerInterface $authorizationChecker,
        IdentifiableObjectRepositoryInterface $attributeRepository,
        IdentifiableObjectRepositoryInterface $localeRepository
    ) {
        $this->beConstructedWith($authorizationChecker, $attributeRepository, $localeRepository);
    }

    function it_is_a_missing_required_attributes_normalizer()
    {
        $this->shouldImplement(MissingRequiredAttributesNormalizerInterface::class);
    }

    function it_filters_missing_attribute_codes_when_user_is_not_granted_edit_permission(
        AuthorizationCheckerInterface $authorizationChecker,
        IdentifiableObjectRepositoryInterface $attributeRepository,
        IdentifiableObjectRepositoryInterface $localeRepository
    ) {
        $marketing = new AttributeGroup();
        $marketing->setCode('marketing');
        $authorizationChecker->isGranted(Attributes::EDIT_ATTRIBUTES, $marketing)->willReturn(false);

        $technical = new AttributeGroup();
        $technical->setCode('technical');
        $authorizationChecker->isGranted(Attributes::EDIT_ATTRIBUTES, $technical)->willReturn(true);

        $name = new Attribute();
        $name->setCode('name');
        $name->setGroup($marketing);
        $attributeRepository->findOneByIdentifier('name')->willReturn($name);

        $size = new Attribute();
        $size->setCode('size');
        $size->setGroup($technical);
        $attributeRepository->findOneByIdentifier('size')->willReturn($size);

        $weight = new Attribute();
        $weight->setCode('weight');
        $weight->setGroup($technical);
        $attributeRepository->findOneByIdentifier('weight')->willReturn($weight);

        $localeRepository->findOneByIdentifier(Argument::type('string'))->willReturn(new Locale());
        $authorizationChecker->isGranted(Attributes::EDIT_ITEMS, Argument::type(LocaleInterface::class))->shouldNotBeCalled();

        $completeness = new ProductCompletenessWithMissingAttributeCodesCollection(
            42,
            [
                new ProductCompletenessWithMissingAttributeCodes('ecommerce', 'en_US', 5, ['name', 'size']),
                new ProductCompletenessWithMissingAttributeCodes('ecommerce', 'fr_FR', 5, ['name', 'weight']),
                new ProductCompletenessWithMissingAttributeCodes('mobile', 'en_US', 4, ['name']),
            ]
        );

        $this->normalize($completeness)->shouldReturn(
            [
                [
                    'channel' => 'ecommerce',
                    'locales' => [
                        'en_US' => [
                            'missing' => [
                                ['code' => 'size'],
                            ],
                        ],
                        'fr_FR' => [
                            'missing' => [
                                ['code' => 'weight'],
                            ],
                        ],
                    ],
                ],
                [
                    'channel' => 'mobile',
                    'locales' => [
                        'en_US' => [
                            'missing' => [],
                        ],
                    ],
                ],
            ]
        );
    }

    function it_filters_localizable_attribute_codes_if_user_is_not_granted_edit_right_on_the_locale(
        AuthorizationCheckerInterface $authorizationChecker,
        IdentifiableObjectRepositoryInterface $attributeRepository,
        IdentifiableObjectRepositoryInterface $localeRepository
    ) {
        $enUs = new Locale();
        $enUs->setCode('en_US');
        $localeRepository->findOneByIdentifier('en_US')->willReturn($enUs);
        $authorizationChecker->isGranted(Attributes::EDIT_ITEMS, $enUs)->willReturn(true);

        $frFr = new Locale();
        $frFr->setCode('fr_FR');
        $localeRepository->findOneByIdentifier('fr_FR')->willReturn($frFr);
        $authorizationChecker->isGranted(Attributes::EDIT_ITEMS, $frFr)->willReturn(false);

        $marketing = new AttributeGroup();
        $marketing->setCode('marketing');
        $authorizationChecker->isGranted(Attributes::EDIT_ATTRIBUTES, $marketing)->willReturn(true);

        $description = new Attribute();
        $description->setLocalizable(true);
        $description->setGroup($marketing);
        $attributeRepository->findOneByIdentifier('description')->willReturn($description);

        $completeness = new ProductCompletenessWithMissingAttributeCodesCollection(
            42,
            [
                new ProductCompletenessWithMissingAttributeCodes('ecommerce', 'en_US', 5, ['description']),
                new ProductCompletenessWithMissingAttributeCodes('ecommerce', 'fr_FR', 5, ['description']),
                new ProductCompletenessWithMissingAttributeCodes('mobile', 'en_US', 4, ['description']),
            ]
        );

        $this->normalize($completeness)->shouldReturn(
            [
                [
                    'channel' => 'ecommerce',
                    'locales' => [
                        'en_US' => [
                            'missing' => [
                                ['code' => 'description'],
                            ],
                        ],
                        'fr_FR' => [
                            'missing' => [],
                        ],
                    ],
                ],
                [
                    'channel' => 'mobile',
                    'locales' => [
                        'en_US' => [
                            'missing' => [
                                ['code' => 'description'],
                            ],
                        ],
                    ],
                ],
            ]
        );
    }
}
