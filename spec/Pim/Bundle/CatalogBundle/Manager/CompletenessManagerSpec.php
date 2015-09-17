<?php

namespace spec\Pim\Bundle\CatalogBundle\Manager;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\AbstractQuery;
use Doctrine\ORM\QueryBuilder;
use PhpSpec\ObjectBehavior;
use Pim\Bundle\CatalogBundle\Doctrine\CompletenessGeneratorInterface;
use Pim\Bundle\CatalogBundle\Model\AttributeInterface;
use Pim\Bundle\CatalogBundle\Model\AttributeRequirementInterface;
use Pim\Bundle\CatalogBundle\Model\ChannelInterface;
use Pim\Bundle\CatalogBundle\Model\CompletenessInterface;
use Pim\Bundle\CatalogBundle\Model\FamilyInterface;
use Pim\Bundle\CatalogBundle\Model\LocaleInterface;
use Pim\Bundle\CatalogBundle\Model\ProductInterface;
use Pim\Bundle\CatalogBundle\Model\ProductValueInterface;
use Pim\Bundle\CatalogBundle\Repository\ChannelRepositoryInterface;
use Pim\Bundle\CatalogBundle\Repository\FamilyRepositoryInterface;
use Pim\Bundle\CatalogBundle\Repository\LocaleRepositoryInterface;
use Pim\Component\Catalog\Completeness\Checker\ProductValueCompleteCheckerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class CompletenessManagerSpec extends ObjectBehavior
{
    function let(
        FamilyRepositoryInterface $familyRepository,
        ChannelRepositoryInterface $channelRepository,
        LocaleRepositoryInterface $localeRepository,
        CompletenessGeneratorInterface $generator,
        ProductValueCompleteCheckerInterface $productValueCompleteChecker
    ) {
        $this->beConstructedWith(
            $familyRepository,
            $channelRepository,
            $localeRepository,
            $generator,
            $productValueCompleteChecker,
            'Pim\Bundle\CatalogBundle\Entity\Channel'
        );
    }

    function it_provide_product_completeness_if_a_family_is_defined_and_attribute_is_locale_specific(
        $familyRepository,
        $productValueCompleteChecker,
        QueryBuilder $qb,
        AbstractQuery $query,
        AttributeRequirementInterface $requirement,
        ProductInterface $product,
        ChannelInterface $mobile,
        LocaleInterface $en,
        FamilyInterface $shirt,
        CompletenessInterface $completeness,
        ProductValueInterface $nameValue,
        AttributeInterface $name
    ) {
        $product->getFamily()->willReturn($shirt);
        $product->getCompletenesses()->willReturn([$completeness]);
        $en->getCode()->willReturn('en_US');
        $mobile->getCode()->willReturn('mobile');

        $completeness->getLocale()->willReturn($en);
        $completeness->getChannel()->willReturn($mobile);
        $completeness->getMissingCount()->willReturn(1);

        $familyRepository->getFullRequirementsQB($shirt, 'en_US')->willReturn($qb);
        $qb->getQuery()->willReturn($query);
        $query->getResult()->willReturn([$requirement]);

        $requirement->getChannel()->willReturn($mobile);
        $requirement->getAttribute()->willReturn($name);
        $requirement->isRequired()->willReturn(true);
        $name->getCode()->willReturn('name');
        $name->isLocalizable()->willreturn(true);
        $name->isScopable()->willReturn(false);
        $name->isLocaleSpecific()->willReturn(true);
        $name->hasLocaleSpecific($en)->willReturn(true);

        $product->getValues()->willReturn(new ArrayCollection());
        $productValueCompleteChecker->supportsValue($nameValue);
        $productValueCompleteChecker->isComplete($nameValue, $mobile, $en);

        $this->getProductCompleteness($product, [$mobile], [$en], 'en_US')->shouldReturn([
            'en_US' => [
                'channels' => [
                    'mobile' => [
                        'completeness' => $completeness,
                        'missing' => [
                            $name
                        ],
                    ],
                ],
                'stats' => [
                    'total' => 1,
                    'complete' => 0,
                ],
            ],
        ]);
    }

    function it_provide_product_completeness_if_a_family_is_defined(
        $familyRepository,
        $productValueCompleteChecker,
        QueryBuilder $qb,
        AbstractQuery $query,
        AttributeRequirementInterface $requirement,
        ProductInterface $product,
        ChannelInterface $mobile,
        LocaleInterface $en,
        FamilyInterface $shirt,
        CompletenessInterface $completeness,
        ProductValueInterface $nameValue,
        AttributeInterface $name
    ) {
        $product->getFamily()->willReturn($shirt);
        $product->getCompletenesses()->willReturn([$completeness]);
        $en->getCode()->willReturn('en_US');
        $mobile->getCode()->willReturn('mobile');

        $completeness->getLocale()->willReturn($en);
        $completeness->getChannel()->willReturn($mobile);
        $completeness->getMissingCount()->willReturn(1);

        $familyRepository->getFullRequirementsQB($shirt, 'en_US')->willReturn($qb);
        $qb->getQuery()->willReturn($query);
        $query->getResult()->willReturn([$requirement]);

        $requirement->getChannel()->willReturn($mobile);
        $requirement->getAttribute()->willReturn($name);
        $requirement->isRequired()->willReturn(true);
        $name->getCode()->willReturn('name');
        $name->isLocalizable()->willreturn(true);
        $name->isScopable()->willReturn(false);
        $name->isLocaleSpecific()->willReturn(true);
        $name->hasLocaleSpecific($en)->willReturn(false);

        $product->getValues()->willReturn(new ArrayCollection());
        $productValueCompleteChecker->supportsValue($nameValue);
        $productValueCompleteChecker->isComplete($nameValue, $mobile, $en);

        $this->getProductCompleteness($product, [$mobile], [$en], 'en_US')->shouldReturn([
            'en_US' => [
                'channels' => [
                    'mobile' => [
                        'completeness' => $completeness,
                        'missing' => [],
                    ],
                ],
                'stats' => [
                    'total' => 1,
                    'complete' => 0,
                ],
            ],
        ]);
    }

    function it_provide_product_completeness_if_family_is_not_defined(
        ProductInterface $product,
        ChannelInterface $mobile,
        LocaleInterface $en,
        FamilyInterface $shirt
    ) {
        $product->getFamily()->willReturn(null);
        $en->getCode()->willReturn('en_US');
        $mobile->getCode()->willReturn('mobile');

        $this->getProductCompleteness($product, [$mobile], [$en], 'en_US')->shouldReturn([
            'en_US' => [
                'channels' => [
                    'mobile' => [
                        'completeness' => null,
                        'missing' => [],
                    ],
                ],
                'stats' => [
                    'total' => 0,
                    'complete' => 0,
                ],
            ],
        ]);
    }
}
