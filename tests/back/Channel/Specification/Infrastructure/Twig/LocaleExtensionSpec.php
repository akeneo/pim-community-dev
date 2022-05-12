<?php

namespace Specification\Akeneo\Channel\Infrastructure\Twig;

use Akeneo\Channel\Infrastructure\Component\Model\LocaleInterface;
use Akeneo\UserManagement\Bundle\Context\UserContext;
use PhpSpec\ObjectBehavior;
use Twig\Node\Node;
use Twig\TwigFilter;
use Twig\TwigFunction;

class LocaleExtensionSpec extends ObjectBehavior
{
    function let(UserContext $userContext, LocaleInterface $en, LocaleInterface $fr)
    {
        $this->beConstructedWith($userContext);
        $en->getCode()->willReturn('en_US');
        $fr->getCode()->willReturn('fr_FR');
    }

    function it_registers_locale_functions()
    {
        $functions = $this->getFunctions();

        $functions->shouldHaveCount(4);
        $functions->shouldHaveTwigMethod('locale_code', 'currentLocaleCode');
        $functions->shouldHaveTwigMethod('locale_label', 'localeLabel');
        $functions->shouldHaveTwigMethod('currency_symbol', 'currencySymbol');
        $functions->shouldHaveTwigMethod('currency_label', 'currencyLabel');
    }

    function it_registers_a_locale_filter()
    {
        $functions = $this->getFilters();

        $functions->shouldHaveCount(1);
        $functions->shouldHaveTwigFilter('flag', 'flag', ['html'], true);
    }

    function it_provides_current_locale_code($userContext, LocaleInterface $en)
    {
        $userContext->getCurrentLocale()->willReturn($en);
        $this->currentLocaleCode()->shouldReturn('en_US');
    }

    function it_provides_a_locale_label_translated_in_the_specified_locale($userContext, LocaleInterface $fr)
    {
        $userContext->getCurrentLocale()->willReturn($fr);

        $this->localeLabel('fr', 'en_US')->shouldReturn('French');
    }

    function it_provides_a_currency_symbol_translated_in_the_specified_locale($userContext, LocaleInterface $en)
    {
        $userContext->getCurrentLocale()->willReturn($en);
        $this->currencySymbol('USD')->shouldReturn('$');
        $this->currencySymbol('USD', 'fr_FR')->shouldReturn('$US');

    }

    function it_provides_a_currency_label_translated_in_the_specified_locale($userContext, LocaleInterface $en)
    {
        $userContext->getCurrentLocale()->willReturn($en);
        $this->currencyLabel('USD')->shouldReturn('US Dollar');
        $this->currencyLabel('USD', 'fr_FR')->shouldReturn('dollar des États-Unis');

    }

    function it_returns_null_when_the_currency_label_is_not_found($userContext, LocaleInterface $en)
    {
        $userContext->getCurrentLocale()->willReturn($en);
        $this->currencyLabel('XSU')->shouldReturn(null);
        $this->currencyLabel('XSU', 'fr_FR')->shouldReturn(null);
    }

    function getMatchers(): array
    {
        $filterArgs = new Node();

        return [
            'haveTwigMethod' => function ($subject, $name, $method) {
                $function = array_filter(
                    $subject,
                    function ($function) use ($name) {
                        return $function instanceof TwigFunction &&
                            $function->getName() === $name;
                    }
                );

                if (count($function) !== 1) {
                    return false;
                }

                $function = array_shift($function);

                return $function->getCallable() === [$this->getWrappedObject(), $method];
            },
            'haveTwigFilter' => function ($subject, $name, $method, $isSafe, $needsEnvironment) use ($filterArgs) {
                $filter = array_filter(
                    $subject,
                    function ($filter) use ($name) {
                        return $filter instanceof TwigFilter &&
                            $filter->getName() === $name;
                    }
                );

                if (count($filter) !== 1) {
                    return false;
                }

                $filter = array_shift($filter);

                return $filter->getCallable() === [$this->getWrappedObject(), $method]
                    && $filter->needsEnvironment() === $needsEnvironment
                    && $filter->getSafe($filterArgs) === $isSafe;
            },
        ];
    }
}
