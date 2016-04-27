<?php

namespace spec\PimEnterprise\Bundle\CatalogRuleBundle\Twig;

use Akeneo\Component\Localization\Presenter\PresenterInterface;
use Pim\Component\Catalog\Localization\Presenter\PresenterRegistryInterface;
use PhpSpec\ObjectBehavior;
use Pim\Bundle\EnrichBundle\Resolver\LocaleResolver;
use Prophecy\Argument;

class RuleExtensionSpec extends ObjectBehavior
{
    function let(PresenterRegistryInterface $presenterRegistry, LocaleResolver $localeResolver)
    {
        $this->beConstructedWith($presenterRegistry, $localeResolver);
    }

    function it_is_a_twig_extension()
    {
        $this->shouldHaveType('\Twig_Extension');
    }

    function it_has_a_name()
    {
        $this->getName()->shouldReturn('pimee_catalog_rule_rule_extension');
    }

    function it_defines_filters()
    {
        $filters = $this->getFilters();

        $filters->shouldHaveCount(2);

        $filters[0]->shouldBeAnInstanceOf('\Twig_SimpleFilter');
        $filters[0]->getName()->shouldReturn('present_rule_action_value');

        $filters[1]->shouldBeAnInstanceOf('\Twig_SimpleFilter');
        $filters[1]->getName()->shouldReturn('append_locale_and_scope_context');
    }

    function it_presents_rule_action_with_scalar_value()
    {
        $this->presentRuleActionValue('toto', 'unknown')->shouldReturn('toto');
    }

    function it_presents_rule_action_with_boolean_value()
    {
        $this->presentRuleActionValue(true, 'unknown')->shouldReturn('true');
    }

    function it_presents_rule_action_with_array_value()
    {
        $this->presentRuleActionValue(['foo', 'bar'], 'unknown')->shouldReturn('foo, bar');
    }

    function it_presents_rule_action_with_scalar_value_using_presenter(
        $presenterRegistry,
        $localeResolver,
        PresenterInterface $presenter
    ) {
        $presenterRegistry->getPresenterByFieldCode('attribute_code')->willReturn($presenter);
        $localeResolver->getCurrentLocale()->willReturn('en_US');
        $presenter->present('toto', ['locale' => 'en_US'])->willReturn('expected');

        $this->presentRuleActionValue('toto', 'attribute_code')->shouldReturn('expected');
    }

    function it_presents_rule_action_with_boolean_value_using_presenter(
        $presenterRegistry,
        $localeResolver,
        PresenterInterface $presenter
    ) {
        $presenterRegistry->getPresenterByFieldCode('attribute_code')->willReturn($presenter);
        $localeResolver->getCurrentLocale()->willReturn('en_US');
        $presenter->present(true, ['locale' => 'en_US'])->willReturn('expected');

        $this->presentRuleActionValue(true, 'attribute_code')->shouldReturn('expected');
    }

    function it_presents_rule_action_with_array_value_using_presenter(
        $presenterRegistry,
        $localeResolver,
        PresenterInterface $presenter
    ) {
        $presenterRegistry->getPresenterByFieldCode('attribute_code')->willReturn($presenter);
        $localeResolver->getCurrentLocale()->willReturn('en_US');
        $presenter->present(['foo', 'bar'], ['locale' => 'en_US'])->willReturn('foobar');

        $this->presentRuleActionValue(['foo', 'bar'], 'attribute_code')->shouldReturn('foobar');
    }

    function it_presents_rule_action_with_array_value_using_presenter_returning_array(
        $presenterRegistry,
        $localeResolver,
        PresenterInterface $presenter
    ) {
        $presenterRegistry->getPresenterByFieldCode('attribute_code')->willReturn($presenter);
        $localeResolver->getCurrentLocale()->willReturn('en_US');
        $presenter->present(['foo', 'bar'], ['locale' => 'en_US'])->willReturn(['presented foo', 'presented bar']);

        $this->presentRuleActionValue(['foo', 'bar'], 'attribute_code')->shouldReturn('presented foo, presented bar');
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
