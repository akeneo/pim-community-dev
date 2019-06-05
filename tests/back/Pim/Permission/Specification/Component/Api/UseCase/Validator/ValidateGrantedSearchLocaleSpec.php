<?php

declare(strict_types=1);

namespace Specification\Akeneo\Pim\Permission\Component\Api\UseCase\Validator;


use Akeneo\Channel\Component\Model\Locale;
use Akeneo\Tool\Component\Api\Exception\InvalidQueryException;
use Akeneo\Pim\Enrichment\Component\Product\Connector\UseCase\Validator\ValidateGrantedSearchLocaleInterface;
use Akeneo\Pim\Permission\Component\Api\UseCase\Validator\ValidateGrantedSearchLocale;
use Akeneo\Pim\Permission\Component\Attributes;
use Akeneo\Tool\Component\StorageUtils\Repository\IdentifiableObjectRepositoryInterface;
use PhpSpec\ObjectBehavior;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

class ValidateGrantedSearchLocaleSpec extends ObjectBehavior
{
    function let(
        IdentifiableObjectRepositoryInterface $localeRepository,
        AuthorizationCheckerInterface $authorizationChecker
    ) {
        $this->beConstructedWith($localeRepository, $authorizationChecker);
    }

    function it_is_a_granted_search_validator()
    {
        $this->shouldBeAnInstanceOf(ValidateGrantedSearchLocale::class);
        $this->shouldImplement(ValidateGrantedSearchLocaleInterface::class);
    }

    function it_is_valid_when_search_locale_is_not_provided()
    {
        $this->shouldNotThrow(InvalidQueryException::class)->during('validate', [[], null]);
    }

    function it_is_valid_when_search_locale_is_granted(IdentifiableObjectRepositoryInterface $localeRepository, AuthorizationCheckerInterface $authorizationChecker)
    {
        $locale = new Locale();
        $localeRepository->findOneByIdentifier('en_US')->willReturn($locale);
        $authorizationChecker->isGranted(Attributes::VIEW_ITEMS, $locale)->willReturn(true);

        $this->shouldNotThrow(InvalidQueryException::class)->during('validate', [[], 'en_US']);
    }

    function it_throws_an_exception_when_search_locale_is_not_granted(IdentifiableObjectRepositoryInterface $localeRepository, AuthorizationCheckerInterface $authorizationChecker)
    {
        $locale = new Locale();
        $localeRepository->findOneByIdentifier('en_US')->willReturn($locale);
        $authorizationChecker->isGranted(Attributes::VIEW_ITEMS, $locale)->willReturn(false);

        $this->shouldThrow(InvalidQueryException::class)->during('validate', [[], 'en_US']);
    }

    function it_throws_an_exception_when_a_locale_in_completeness_filter_is_not_granted(IdentifiableObjectRepositoryInterface $localeRepository, AuthorizationCheckerInterface $authorizationChecker)
    {
        $locale = new Locale();
        $localeRepository->findOneByIdentifier('en_US')->willReturn($locale);
        $authorizationChecker->isGranted(Attributes::VIEW_ITEMS, $locale)->willReturn(false);

        $this->shouldThrow(InvalidQueryException::class)->during('validate', [
            [
                'completeness' => [['locales' => ['en_US']]]],
                null
            ]
        );
    }

    function it_throws_an_exception_when_a_locale_in_locales_filter_is_not_granted(IdentifiableObjectRepositoryInterface $localeRepository, AuthorizationCheckerInterface $authorizationChecker)
    {
        $locale = new Locale();
        $localeRepository->findOneByIdentifier('en_US')->willReturn($locale);
        $authorizationChecker->isGranted(Attributes::VIEW_ITEMS, $locale)->willReturn(false);

        $this->shouldThrow(InvalidQueryException::class)->during('validate', [
            [
                'completeness' => [['locale' => 'en_US']]],
                null
            ]
        );
    }
}
