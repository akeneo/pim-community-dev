<?php

declare(strict_types=1);

namespace Specification\Akeneo\Pim\Enrichment\Component\Product\Validator;

use Akeneo\Pim\Enrichment\Component\Product\Exception\UnavailableLocaleException;
use Akeneo\Pim\Enrichment\Component\Product\Exception\UnavailableSpecificLocaleException;
use Akeneo\Pim\Enrichment\Component\Product\Validator\AttributeValidatorHelper;
use Akeneo\Pim\Enrichment\Component\Product\Validator\ElasticsearchFilterValidator;
use Akeneo\Pim\Structure\Component\Model\AttributeInterface;
use Akeneo\Tool\Component\StorageUtils\Repository\IdentifiableObjectRepositoryInterface;
use PhpSpec\ObjectBehavior;

class ElasticsearchFilterValidatorSpec extends ObjectBehavior
{
    function let(
        IdentifiableObjectRepositoryInterface $attributeRepository,
        AttributeValidatorHelper $attributeValidator
    ) {
        $this->beConstructedWith($attributeRepository, $attributeValidator);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(ElasticsearchFilterValidator::class);
    }

    function it_catches_the_exception_when_the_specific_locale_is_unavailable(
        IdentifiableObjectRepositoryInterface $attributeRepository,
        AttributeValidatorHelper $attributeValidator,
        AttributeInterface $attribute
    ) {
        $attributeRepository->findOneByIdentifier('attributeCode')
            ->willReturn($attribute);
        $attributeValidator->validateLocale($attribute, 'it_IT')
            ->willThrow(UnavailableSpecificLocaleException::class);

        $this->validateLocaleForAttribute('attributeCode', 'it_IT');
    }

    function it_does_not_catch_other_exceptions(
        IdentifiableObjectRepositoryInterface $attributeRepository,
        AttributeValidatorHelper $attributeValidator,
        AttributeInterface $attribute
    ) {
        $attributeRepository->findOneByIdentifier('attributeCode')
            ->willReturn($attribute);
        $attributeValidator->validateLocale($attribute, 'de_DE')
            ->willThrow(UnavailableLocaleException::class);

        $this->shouldThrow(UnavailableLocaleException::class)
            ->during('validateLocaleForAttribute', ['attributeCode', 'de_DE']);
    }
}
