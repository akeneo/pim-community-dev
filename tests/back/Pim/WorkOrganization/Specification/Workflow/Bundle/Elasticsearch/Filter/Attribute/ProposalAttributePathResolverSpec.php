<?php

namespace Specification\Akeneo\Pim\WorkOrganization\Workflow\Bundle\Elasticsearch\Filter\Attribute;

use Akeneo\Pim\Structure\Component\Model\AttributeInterface;
use Akeneo\Channel\Infrastructure\Component\Model\ChannelInterface;
use Akeneo\Channel\Infrastructure\Component\Repository\ChannelRepositoryInterface;
use Akeneo\Channel\Infrastructure\Component\Repository\LocaleRepositoryInterface;
use Akeneo\Pim\WorkOrganization\Workflow\Bundle\Elasticsearch\Filter\Attribute\ProposalAttributePathResolver;
use PhpSpec\ObjectBehavior;

class ProposalAttributePathResolverSpec extends ObjectBehavior
{
    function let(ChannelRepositoryInterface $channelRepository, LocaleRepositoryInterface $localeRepository)
    {
        $this->beConstructedWith($channelRepository, $localeRepository);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(ProposalAttributePathResolver::class);
    }

    function it_returns_paths_for_attribute_localizable_and_scopable(
        $channelRepository,
        AttributeInterface $textAttribute,
        ChannelInterface $channelEcommerce,
        ChannelInterface $channelTablet
    ) {
        $textAttribute->getCode()->willReturn('text');
        $textAttribute->getBackendType()->willReturn('textarea');
        $textAttribute->isScopable()->willReturn(true);
        $textAttribute->isLocaleSpecific()->willReturn(false);
        $textAttribute->isLocalizable()->willReturn(true);

        $channelRepository->findAll()->willReturn([$channelEcommerce, $channelTablet]);
        $channelEcommerce->getCode()->willReturn('ecommerce');
        $channelEcommerce->getLocaleCodes()->willReturn(['en_US', 'fr_FR']);
        $channelTablet->getCode()->willReturn('tablet');
        $channelTablet->getLocaleCodes()->willReturn(['en_US', 'fr_FR', 'it_IT']);

        $this->getAttributePaths($textAttribute)->shouldReturn([
            'values.text-textarea.ecommerce.en_US',
            'values.text-textarea.ecommerce.fr_FR',
            'values.text-textarea.tablet.en_US',
            'values.text-textarea.tablet.fr_FR',
            'values.text-textarea.tablet.it_IT'
        ]);
    }

    function it_returns_paths_for_attribute_localizable_but_not_scopable(
        $localeRepository,
        AttributeInterface $textAttribute
    ) {
        $textAttribute->getCode()->willReturn('text');
        $textAttribute->getBackendType()->willReturn('textarea');
        $textAttribute->isScopable()->willReturn(false);
        $textAttribute->isLocaleSpecific()->willReturn(false);
        $textAttribute->isLocalizable()->willReturn(true);

        $localeRepository->getActivatedLocaleCodes()->willReturn(['en_US', 'fr_FR', 'it_IT']);

        $this->getAttributePaths($textAttribute)->shouldReturn([
            'values.text-textarea.<all_channels>.en_US',
            'values.text-textarea.<all_channels>.fr_FR',
            'values.text-textarea.<all_channels>.it_IT'
        ]);
    }

    function it_returns_paths_for_attribute_locale_specific_non_scopable(AttributeInterface $textAttribute)
    {
        $textAttribute->getCode()->willReturn('text');
        $textAttribute->getBackendType()->willReturn('textarea');
        $textAttribute->isScopable()->willReturn(false);
        $textAttribute->isLocaleSpecific()->willReturn(true);
        $textAttribute->isLocalizable()->willReturn(false);

        $textAttribute->getAvailableLocaleCodes()->willReturn(['fr_FR', 'it_IT']);

        $this->getAttributePaths($textAttribute)->shouldReturn([
            'values.text-textarea.<all_channels>.fr_FR',
            'values.text-textarea.<all_channels>.it_IT'
        ]);
    }

    function it_returns_paths_for_attribute_locale_specific_and_scopable(
        $channelRepository,
        AttributeInterface $textAttribute,
        ChannelInterface $channelEcommerce,
        ChannelInterface $channelTablet
    ) {
        $textAttribute->getCode()->willReturn('text');
        $textAttribute->getBackendType()->willReturn('textarea');
        $textAttribute->isScopable()->willReturn(true);
        $textAttribute->isLocaleSpecific()->willReturn(true);
        $textAttribute->isLocalizable()->willReturn(false);

        $channelRepository->findAll()->willReturn([$channelEcommerce, $channelTablet]);
        $channelEcommerce->getCode()->willReturn('ecommerce');
        $channelTablet->getCode()->willReturn('tablet');

        $textAttribute->getAvailableLocaleCodes()->willReturn(['fr_FR', 'it_IT']);

        $this->getAttributePaths($textAttribute)->shouldReturn([
            'values.text-textarea.ecommerce.fr_FR',
            'values.text-textarea.ecommerce.it_IT',
            'values.text-textarea.tablet.fr_FR',
            'values.text-textarea.tablet.it_IT'
        ]);
    }

    function it_returns_paths_for_attribute_non_localizable_and_non_scopable(AttributeInterface $textAttribute)
    {
        $textAttribute->getCode()->willReturn('text');
        $textAttribute->getBackendType()->willReturn('textarea');
        $textAttribute->isScopable()->willReturn(false);
        $textAttribute->isLocaleSpecific()->willReturn(false);
        $textAttribute->isLocalizable()->willReturn(false);

        $this->getAttributePaths($textAttribute)->shouldReturn([
            'values.text-textarea.<all_channels>.<all_locales>'
        ]);
    }
}
