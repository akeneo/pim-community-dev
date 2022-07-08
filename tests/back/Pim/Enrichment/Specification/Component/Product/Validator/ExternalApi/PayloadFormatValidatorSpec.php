<?php

declare(strict_types=1);

namespace Specification\Akeneo\Pim\Enrichment\Component\Product\Validator\ExternalApi;

use Akeneo\Pim\Enrichment\Component\Product\Validator\ExternalApi\PayloadFormat;
use Akeneo\Pim\Enrichment\Component\Product\Validator\ExternalApi\PayloadFormatValidator;
use Akeneo\Pim\Structure\Component\AttributeTypes;
use Akeneo\Pim\Structure\Component\Repository\AttributeRepositoryInterface;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Symfony\Component\Validator\Constraints\Type;
use Symfony\Component\Validator\ConstraintValidatorInterface;
use Symfony\Component\Validator\Context\ExecutionContextInterface;
use Symfony\Component\Validator\Validator\ContextualValidatorInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class PayloadFormatValidatorSpec extends ObjectBehavior
{
    function let(AttributeRepositoryInterface $attributeRepository, ExecutionContextInterface $context)
    {
        $this->beConstructedWith($attributeRepository);
        $this->initialize($context);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(PayloadFormatValidator::class);
        $this->shouldImplement(ConstraintValidatorInterface::class);
    }

    function it_throws_an_error_when_constrain_is_wrong()
    {
        $this->shouldThrow(\InvalidArgumentException::class)->during('validate', [[], new Type('array')]);
    }

    function it_fetches_attribute_types_and_calls_validation_on_constraints(
        AttributeRepositoryInterface $attributeRepository,
        ExecutionContextInterface $context,
        ValidatorInterface $validator,
        ContextualValidatorInterface $contextualValidator,
    ) {
        $payload = [
            'values' => [
                'sku' => [
                    ['locale' => null, 'scope' => null, 'data' => 'foo'],
                ],
                'price' => [
                    ['locale' => null, 'scope' => null, 'data' => [['amount' => 100, 'currency' => 'USD']]],
                ],
                '5g_enabled' => [
                    ['locale' => null, 'scope' => null, 'data' => true],
                ],
            ],
        ];

        $attributeRepository->getAttributeTypeByCodes(['sku', 'price', '5g_enabled'])->shouldBeCalledOnce()->willReturn([
            'sku' => AttributeTypes::IDENTIFIER,
            'price' => AttributeTypes::PRICE_COLLECTION,
            '5g_enabled' => AttributeTypes::BOOLEAN,
        ]);

        $context->getValidator()->shouldBeCalledOnce()->willReturn($validator);
        $validator->inContext($context)->shouldBeCalledOnce()->willReturn($contextualValidator);
        $contextualValidator->validate($payload, Argument::type('array'))->shouldBeCalledOnce();

        $this->validate($payload, new PayloadFormat());
    }
}
