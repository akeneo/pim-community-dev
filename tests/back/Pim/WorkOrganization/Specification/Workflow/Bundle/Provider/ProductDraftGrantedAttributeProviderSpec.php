<?php

namespace Specification\Akeneo\Pim\WorkOrganization\Workflow\Bundle\Provider;

use Akeneo\Channel\Infrastructure\Component\Model\LocaleInterface;
use Akeneo\Tool\Component\StorageUtils\Repository\IdentifiableObjectRepositoryInterface;
use PhpSpec\ObjectBehavior;
use Akeneo\Pim\Structure\Component\Model\AttributeInterface;
use Akeneo\Pim\WorkOrganization\Workflow\Component\Model\EntityWithValuesDraftInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

class ProductDraftGrantedAttributeProviderSpec extends ObjectBehavior
{
    function let(
        IdentifiableObjectRepositoryInterface $attributeRepository,
        IdentifiableObjectRepositoryInterface $localeRepository,
        AuthorizationCheckerInterface $authorizationChecker
    ) {
        $this->beConstructedWith(
            $attributeRepository,
            $localeRepository,
            $authorizationChecker
        );
    }

    function it_provides_attributes(
        $attributeRepository,
        $authorizationChecker,
        EntityWithValuesDraftInterface $draft,
        AttributeInterface $attribute
    ) {
        $attributeRepository->findOneByIdentifier('attribute')->willReturn($attribute);
        $authorizationChecker->isGranted('VIEW_ATTRIBUTES', $attribute)->willReturn(true);

        $draft->getChanges()->willReturn(['values' => [
            'attribute' => [['data' => 'data', 'scope' => null, 'locale' => null]],
        ]]);

        $draft->hasChanges()->willReturn(true);
        $this->getViewable($draft)->shouldReturn([
            'attribute' => $attribute
        ]);
    }

    function it_filters_invisible_attributes(
        $attributeRepository,
        $authorizationChecker,
        EntityWithValuesDraftInterface $draft,
        AttributeInterface $attribute
    ) {
        $attributeRepository->findOneByIdentifier('attribute')->willReturn($attribute);
        $authorizationChecker->isGranted('VIEW_ATTRIBUTES', $attribute)->willReturn(false);

        $draft->hasChanges()->willReturn(true);
        $draft->getChanges()->willReturn(['values' => [
            'attribute' => [['data' => 'data', 'scope' => null, 'locale' => null]],
        ]]);

        $this->getViewable($draft)->shouldReturn([]);
    }

    function it_filters_invisible_by_locale_attributes(
        $attributeRepository,
        $localeRepository,
        $authorizationChecker,
        EntityWithValuesDraftInterface $draft,
        AttributeInterface $attribute,
        LocaleInterface $locale
    ) {
        $attributeRepository->findOneByIdentifier('attribute')->willReturn($attribute);
        $localeRepository->findOneByIdentifier('en_US')->willReturn($locale);
        $authorizationChecker->isGranted('VIEW_ATTRIBUTES', $attribute)->willReturn(true);
        $authorizationChecker->isGranted('VIEW_ITEMS', $locale)->willReturn(false);
        $attribute->isLocalizable()->willReturn(true);

        $draft->hasChanges()->willReturn(true);
        $draft->getChanges()->willReturn(['values' => [
            'attribute' => [['data' => 'data', 'scope' => null, 'locale' => 'en_US']],
        ]]);

        $this->getViewable($draft)->shouldReturn([]);
    }
}
