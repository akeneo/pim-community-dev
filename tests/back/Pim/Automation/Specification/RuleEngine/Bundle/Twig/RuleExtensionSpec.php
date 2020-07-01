<?php

namespace Specification\Akeneo\Pim\Automation\RuleEngine\Bundle\Twig;

use Akeneo\Pim\Enrichment\Component\Product\Localization\Presenter\PresenterRegistryInterface;
use Akeneo\Pim\Structure\Bundle\Doctrine\ORM\Repository\AttributeRepository;
use Akeneo\Platform\Bundle\UIBundle\Resolver\LocaleResolver;
use Akeneo\Tool\Component\FileStorage\Model\FileInfoInterface;
use Akeneo\Tool\Component\FileStorage\Repository\FileInfoRepositoryInterface;
use Akeneo\Tool\Component\Localization\Presenter\PresenterInterface;
use Akeneo\UserManagement\Component\Model\UserInterface;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Translation\TranslatorInterface;

class RuleExtensionSpec extends ObjectBehavior
{
    function let(
        PresenterRegistryInterface $presenterRegistry,
        LocaleResolver $localeResolver,
        AttributeRepository $attributeRepository,
        TranslatorInterface $translator,
        FileInfoRepositoryInterface $fileInfoRepository,
        TokenStorageInterface $tokenStorage,
        TokenInterface $token,
        UserInterface $user
    ) {
        $user->getTimezone()->willReturn('Europe/Paris');
        $token->getUser()->willReturn($user);
        $tokenStorage->getToken()->willReturn($token);

        $this->beConstructedWith(
            $presenterRegistry,
            $localeResolver,
            $attributeRepository,
            $translator,
            $fileInfoRepository,
            $tokenStorage
        );
    }

    function it_is_a_twig_extension()
    {
        $this->shouldHaveType(\Twig_Extension::class);
    }

    function it_defines_filters()
    {
        $filters = $this->getFilters();

        $filters->shouldHaveCount(3);

        $filters[0]->shouldBeAnInstanceOf(\Twig_SimpleFilter::class);
        $filters[0]->getName()->shouldReturn('present_rule_action_value');

        $filters[1]->shouldBeAnInstanceOf(\Twig_SimpleFilter::class);
        $filters[1]->getName()->shouldReturn('append_locale_and_scope_context');

        $filters[2]->shouldBeAnInstanceOf(\Twig_SimpleFilter::class);
        $filters[2]->getName()->shouldReturn('append_include_children_context');
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
        $presenter->present('toto',
            [
                'attribute' => 'attribute_code',
                'locale' => 'en_US',
                'present_relative_date' => true,
                'timezone' => 'Europe/Paris',
            ]
        )->willReturn('expected');
        $attributeRepository->findMediaAttributeCodes()->willReturn(['media_attribute_code']);

        $this->presentRuleActionValue('toto', 'attribute_code')->shouldReturn('expected');
    }

    function it_presents_rule_action_with_filepath_value(
        $presenterRegistry,
        $fileInfoRepository,
        PresenterInterface $presenter,
        AttributeRepository $attributeRepository,
        FileInfoInterface $fileInfo
    ) {
        $presenterRegistry->getPresenterByFieldCode('media_attribute_code')->willReturn(null);
        $presenterRegistry->getPresenterByAttributeCode('media_attribute_code')->willReturn($presenter);
        $attributeRepository->findMediaAttributeCodes()->willReturn(['media_attribute_code']);

        $fileInfoRepository->findOneByIdentifier('/tmp/akeneo.jpg')->willReturn($fileInfo);
        $fileInfo->getOriginalFilename()->willReturn('akeneo.jpg');

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
        $presenter->present(true,
            [
                'attribute' => 'attribute_code',
                'locale' => 'en_US',
                'present_relative_date' => true,
                'timezone' => 'Europe/Paris',
            ]
        )->willReturn('expected');
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
        $presenter->present(['foo', 'bar'],
            [
                'attribute' => 'attribute_code',
                'locale' => 'en_US',
                'present_relative_date' => true,
                'timezone' => 'Europe/Paris',
            ]
        )->willReturn('foobar');
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
        $presenter->present(['foo', 'bar'],
            [
                'attribute' => 'attribute_code',
                'locale' => 'en_US',
                'present_relative_date' => true,
                'timezone' => 'Europe/Paris',
            ]
        )->willReturn(['presented foo', 'presented bar']);
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

    function it_presents_a_null_value()
    {
        $this->presentRuleActionValue(null, 'categories')->shouldReturn('');
    }

    function it_appends_locale_and_scope()
    {
        $this->appendLocaleAndScopeContext('value', 'en_US', 'mobile')
            ->shouldReturn('value [ <i class="flag flag-us"></i> en | mobile ]');
        $this->appendLocaleAndScopeContext('value', 'az_cyrl_AZ', 'mobile')
            ->shouldReturn('value [ <i class="flag flag-az"></i> az | mobile ]');
    }

    function it_appends_locale_and_scope_without_scope()
    {
        $this->appendLocaleAndScopeContext('value', 'en_US')
            ->shouldReturn('value [ <i class="flag flag-us"></i> en ]');
        $this->appendLocaleAndScopeContext('value', 'az_cyrl_AZ')
            ->shouldReturn('value [ <i class="flag flag-az"></i> az ]');
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
