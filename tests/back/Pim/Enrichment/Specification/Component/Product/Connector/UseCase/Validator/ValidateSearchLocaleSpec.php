<?php

declare(strict_types=1);

namespace Specification\Akeneo\Pim\Enrichment\Component\Product\Connector\UseCase\Validator;

use Akeneo\Channel\Component\Model\LocaleInterface;
use Akeneo\Tool\Component\Api\Exception\InvalidQueryException;
use Akeneo\Tool\Component\StorageUtils\Repository\IdentifiableObjectRepositoryInterface;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class ValidateSearchLocaleSpec extends ObjectBehavior
{
    function let(
        IdentifiableObjectRepositoryInterface $localeRepository
    ) {
        $this->beConstructedWith($localeRepository);
    }

    function it_throws_an_exception_when_locale_is_not_a_string()
    {
        $this->shouldThrow(InvalidQueryException::class)->during('validate', [[
            'propertyCode' => [['locale' => []]]
        ], null]);
    }

    function it_throws_an_exception_when_locales_is_not_an_array()
    {
        $this->shouldThrow(InvalidQueryException::class)->during('validate', [[
            'completeness' => [['locales' => 69]]
        ], null]);
    }

    function it_throws_an_exception_when_locales_contain_non_existing_locale(
        IdentifiableObjectRepositoryInterface $localeRepository
    ) {
        $localeRepository->findOneByIdentifier('zz_ZZ')->willReturn(null)->shouldBeCalled();
        $this->shouldThrow(InvalidQueryException::class)->during('validate', [[
            'completeness' => [['locales' => ['zz_ZZ']]]
        ], null]);
    }

    function it_throws_an_exception_when_locales_contain_non_activated_locale(
        IdentifiableObjectRepositoryInterface $localeRepository,
        LocaleInterface $locale
    ) {
        $localeRepository->findOneByIdentifier('en_US')->willReturn($locale)->shouldBeCalled();
        $locale->isActivated()->willReturn(false)->shouldBeCalled();
        $this->shouldThrow(InvalidQueryException::class)->during('validate', [[
            'completeness' => [['locales' => ['en_US']]]
        ], null]);
    }
    
    function it_throws_an_exception_when_locales_key_is_provided_for_any_filter_except_completeness_filter(
        IdentifiableObjectRepositoryInterface $localeRepository
    ) {
        $localeRepository->findOneByIdentifier(Argument::cetera())->shouldNotBeCalled();
        $this->shouldThrow(InvalidQueryException::class)->during('validate', [[
            'categories' => [['locales' => ['en_US']]]
        ], 'en_US']);
    }

    function it_throws_an_exception_when_default_locale_does_not_exist(
        IdentifiableObjectRepositoryInterface $localeRepository
    ) {
        $localeRepository->findOneByIdentifier('zz_ZZ')->willReturn(null)->shouldBeCalled();
        $this->shouldThrow(InvalidQueryException::class)->during('validate', [[
            'propertyCode' => [[]]
        ], 'zz_ZZ']);
    }

    function it_throws_an_exception_when_default_locale_is_not_activated(
        IdentifiableObjectRepositoryInterface $localeRepository,
        LocaleInterface $locale
    ) {
        $localeRepository->findOneByIdentifier('en_US')->willReturn($locale)->shouldBeCalled();
        $locale->isActivated()->willReturn(false)->shouldBeCalled();
        $this->shouldThrow(InvalidQueryException::class)->during('validate', [[
            'propertyCode' => [[]]
        ], 'en_US']);
    }
}
