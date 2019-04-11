<?php

declare(strict_types=1);

namespace Specification\Akeneo\Pim\Permission\Component\Api\UseCase\Validator;


use Akeneo\Channel\Component\Model\Locale;
use Akeneo\Tool\Component\Api\Exception\InvalidQueryException;
use Akeneo\Pim\Enrichment\Component\Product\Connector\UseCase\Validator\ValidateGrantedLocalesInterface;
use Akeneo\Pim\Permission\Component\Api\UseCase\Validator\ValidateGrantedLocales;
use Akeneo\Pim\Permission\Component\Attributes;
use Akeneo\Tool\Component\StorageUtils\Repository\IdentifiableObjectRepositoryInterface;
use PhpSpec\ObjectBehavior;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

class ValidateGrantedLocalesSpec extends ObjectBehavior
{
    function let(
        IdentifiableObjectRepositoryInterface $localeRepository,
        AuthorizationCheckerInterface $authorizationChecker
    ) {
        $this->beConstructedWith($localeRepository, $authorizationChecker);
    }

    function it_is_a_granted_search_validator()
    {
        $this->shouldBeAnInstanceOf(ValidateGrantedLocales::class);
        $this->shouldImplement(ValidateGrantedLocalesInterface::class);
    }

    function it_is_valid_when_locales_are_not_provided()
    {
        $this->shouldNotThrow(InvalidQueryException::class)->during('validateForLocaleCodes', [null]);
        $this->shouldNotThrow(InvalidQueryException::class)->during('validateForLocaleCodes', [[]]);
    }

    function it_is_valid_when_locales_are_granted(IdentifiableObjectRepositoryInterface $localeRepository, AuthorizationCheckerInterface $authorizationChecker)
    {
        $localeUS = new Locale();
        $localeFR = new Locale();

        $localeRepository->findOneByIdentifier('en_US')->willReturn($localeUS);
        $localeRepository->findOneByIdentifier('fr_FR')->willReturn($localeFR);

        $authorizationChecker->isGranted(Attributes::VIEW_ITEMS, $localeUS)->willReturn(true);
        $authorizationChecker->isGranted(Attributes::VIEW_ITEMS, $localeFR)->willReturn(true);

        $this->shouldNotThrow(InvalidQueryException::class)->during('validateForLocaleCodes', ['en_US', 'fr_FR']);
    }

    function it_throws_an_exception_when_a_locale_is_not_granted(IdentifiableObjectRepositoryInterface $localeRepository, AuthorizationCheckerInterface $authorizationChecker)
    {
        $localeUS = new Locale();
        $localeFR = new Locale();

        $localeRepository->findOneByIdentifier('en_US')->willReturn($localeUS);
        $localeRepository->findOneByIdentifier('fr_FR')->willReturn($localeFR);

        $authorizationChecker->isGranted(Attributes::VIEW_ITEMS, $localeUS)->willReturn(true);
        $authorizationChecker->isGranted(Attributes::VIEW_ITEMS, $localeFR)->willReturn(false);

        $this->shouldNotThrow(InvalidQueryException::class)->during('validateForLocaleCodes', ['en_US', 'fr_FR']);
    }
}
