<?php

namespace spec\Pim\Bundle\EnrichBundle\Twig;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Pim\Bundle\CatalogBundle\Helper\LocaleHelper;

class LocaleExtensionSpec extends ObjectBehavior
{
    function let(LocaleHelper $helper)
    {
        $this->beConstructedWith($helper);
    }

    function it_has_a_name()
    {
        $this->getName()->shouldReturn('pim_locale_extension');
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

    function it_provides_current_locale_code($helper)
    {
        $helper->getCurrentLocaleCode()->willReturn('en_US');

        $this->currentLocaleCode()->shouldReturn('en_US');
    }

    function it_provides_a_locale_label_translated_in_the_specified_locale($helper)
    {
        $helper->getLocaleLabel('fr', 'en_US')->willReturn('French');

        $this->localeLabel('fr', 'en_US')->shouldReturn('French');
    }

    function it_provides_a_currency_symbol_translated_in_the_specified_locale($helper)
    {
        $helper->getCurrencySymbol('fr', 'en_US')->willReturn('EUR');

        $this->currencySymbol('fr', 'en_US')->shouldReturn('EUR');
    }

    function it_provides_a_currency_label_translated_in_the_specified_locale($helper)
    {
        $helper->getCurrencyLabel('fr', 'en_US')->willReturn('Euro');

        $this->currencyLabel('fr', 'en_US')->shouldReturn('Euro');
    }

    public function getMatchers()
    {
        $filterArgs = new \Twig_Node();

        return [
            'haveTwigMethod' => function ($subject, $name, $method) {
                $function = array_filter(
                    $subject,
                    function ($function) use ($name) {
                        return $function instanceof \Twig_SimpleFunction &&
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
                        return $filter instanceof \Twig_SimpleFilter &&
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
