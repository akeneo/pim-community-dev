<?php

namespace spec\PimEnterprise\Bundle\CatalogRuleBundle\Twig;

use Akeneo\Component\Localization\Presenter\PresenterInterface;
use PhpSpec\ObjectBehavior;
use Pim\Bundle\CatalogBundle\Doctrine\ORM\Repository\AttributeRepository;
use Pim\Bundle\EnrichBundle\Resolver\LocaleResolver;
use Pim\Component\Catalog\Localization\Presenter\PresenterRegistryInterface;
use Prophecy\Argument;
use Symfony\Component\Translation\TranslatorInterface;

class RuleExtensionSpec extends ObjectBehavior
{
    function let(
        PresenterRegistryInterface $presenterRegistry,
        LocaleResolver $localeResolver,
        AttributeRepository $attributeRepository,
        TranslatorInterface $translator
    ) {
        $this->beConstructedWith($presenterRegistry, $localeResolver, $attributeRepository, $translator);
    }

    function it_is_a_twig_extension()
    {
        $this->shouldHaveType('\Twig_Extension');
    }

    function it_defines_filters()
    {
        $filters = $this->getFilters();

        $filters->shouldHaveCount(3);

        $filters[0]->shouldBeAnInstanceOf('\Twig_SimpleFilter');
        $filters[0]->getName()->shouldReturn('present_rule_action_value');

        $filters[1]->shouldBeAnInstanceOf('\Twig_SimpleFilter');
        $filters[1]->getName()->shouldReturn('append_locale_and_scope_context');

        $filters[2]->shouldBeAnInstanceOf('\Twig_SimpleFilter');
        $filters[2]->getName()->shouldReturn('append_apply_children_context');
    }

    function it_presents_rule_action_with_scalar_value(AttributeRepository $attributeRepository)
    {
        $attributeRepository->findMediaAttributeCodes()->willReturn(['media_attribute_code']);
        $this->presentRuleActionValue('toto', 'unknown')->shouldReturn('toto');
    }

    function it_presents_rule_action_with_array_value(AttributeRepository $attributeRepository)
    {
        $attributeRepository->findMediaAttributeCodes()->willReturn(['media_attribute_code']);

        $this->presentRuleActionValue(['foo', 'bar'], 'unknown')->shouldReturn('foo, bar');
    }

    function it_presents_rule_action_with_scalar_value_using_presenter(
        $presenterRegistry,
        $localeResolver,
        PresenterInterface $presenter,
        AttributeRepository $attributeRepository
    ) {
        $presenterRegistry->getPresenterByFieldCode('attribute_code')->willReturn(null);
        $presenterRegistry->getPresenterByAttributeCode('attribute_code')->willReturn($presenter);
        $localeResolver->getCurrentLocale()->willReturn('en_US');
        $presenter->present('toto', ['locale' => 'en_US'])->willReturn('expected');
        $attributeRepository->findMediaAttributeCodes()->willReturn(['media_attribute_code']);

        $this->presentRuleActionValue('toto', 'attribute_code')->shouldReturn('expected');
    }

    function it_presents_rule_action_with_filepath_value(
        $presenterRegistry,
        PresenterInterface $presenter,
        AttributeRepository $attributeRepository
    ) {
        $presenterRegistry->getPresenterByFieldCode('media_attribute_code')->willReturn(null);
        $presenterRegistry->getPresenterByAttributeCode('media_attribute_code')->willReturn($presenter);
        $attributeRepository->findMediaAttributeCodes()->willReturn(['media_attribute_code']);

        $this->presentRuleActionValue('/tmp/akeneo.jpg', 'media_attribute_code')
            ->shouldReturn(sprintf('<i class="icon-file"></i> %s', 'akeneo.jpg'));
    }

    function it_presents_rule_action_with_boolean_value_using_presenter(
        $presenterRegistry,
        $localeResolver,
        PresenterInterface $presenter,
        AttributeRepository $attributeRepository
    ) {
        $presenterRegistry->getPresenterByFieldCode('attribute_code')->willReturn(null);
        $presenterRegistry->getPresenterByAttributeCode('attribute_code')->willReturn($presenter);
        $localeResolver->getCurrentLocale()->willReturn('en_US');
        $presenter->present(true, ['locale' => 'en_US'])->willReturn('expected');
        $attributeRepository->findMediaAttributeCodes()->willReturn(['media_attribute_code']);

        $this->presentRuleActionValue(true, 'attribute_code')->shouldReturn('expected');
    }

    function it_presents_rule_action_with_array_value_using_presenter(
        $presenterRegistry,
        $localeResolver,
        PresenterInterface $presenter,
        AttributeRepository $attributeRepository
    ) {
        $presenterRegistry->getPresenterByFieldCode('attribute_code')->willReturn(null);
        $presenterRegistry->getPresenterByAttributeCode('attribute_code')->willReturn($presenter);
        $localeResolver->getCurrentLocale()->willReturn('en_US');
        $presenter->present(['foo', 'bar'], ['locale' => 'en_US'])->willReturn('foobar');
        $attributeRepository->findMediaAttributeCodes()->willReturn(['media_attribute_code']);

        $this->presentRuleActionValue(['foo', 'bar'], 'attribute_code')->shouldReturn('foobar');
    }

    function it_presents_rule_action_with_array_value_using_presenter_returning_array(
        $presenterRegistry,
        $localeResolver,
        PresenterInterface $presenter,
        AttributeRepository $attributeRepository
    ) {
        $presenterRegistry->getPresenterByFieldCode('attribute_code')->willReturn(null);
        $presenterRegistry->getPresenterByAttributeCode('attribute_code')->willReturn($presenter);
        $localeResolver->getCurrentLocale()->willReturn('en_US');
        $presenter->present(['foo', 'bar'], ['locale' => 'en_US'])->willReturn(['presented foo', 'presented bar']);
        $attributeRepository->findMediaAttributeCodes()->willReturn(['media_attribute_code']);

        $this->presentRuleActionValue(['foo', 'bar'], 'attribute_code')->shouldReturn('presented foo, presented bar');
    }

    function it_presents_rule_action_with_field(
        $presenterRegistry,
        PresenterInterface $presenter,
        AttributeRepository $attributeRepository
    ) {
        $presenterRegistry->getPresenterByFieldCode('enabled')->willReturn($presenter);
        $presenter->present(false, Argument::any())->willReturn('false');
        $attributeRepository->findMediaAttributeCodes()->willReturn(['media_attribute_code']);

        $this->presentRuleActionValue(false, 'enabled')->shouldReturn('false');
    }

    function it_appends_locale_and_scope()
    {
        $this->appendLocaleAndScopeContext('value', 'en_US', 'mobile')
            ->shouldReturn('value [ <i class="flag flag-us"></i> en | mobile ]');
    }

    function it_appends_locale_and_scope_without_scope()
    {
        $this->appendLocaleAndScopeContext('value', 'en_US')
            ->shouldReturn('value [ <i class="flag flag-us"></i> en ]');
    }

    function it_appends_locale_and_scope_without_locale()
    {
        $this->appendLocaleAndScopeContext('value', '', 'mobile')->shouldReturn('value [ mobile ]');
    }

    function it_appends_locale_and_scope_without_locale_and_scope()
    {
        $this->appendLocaleAndScopeContext('value')->shouldReturn('value');
    }
}
