<?php

namespace spec\Akeneo\Tool\Component\Api\Normalizer\Exception;

use PhpSpec\ObjectBehavior;
use Akeneo\Tool\Component\Api\Exception\ViolationHttpException;
use Akeneo\Pim\Structure\Component\Model\AttributeInterface;
use Akeneo\Tool\Component\StorageUtils\Repository\IdentifiableObjectRepositoryInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\EntityWithValuesInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\WriteValueCollection;
use Akeneo\Pim\Enrichment\Component\Product\Model\ValueInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintViolation;
use Symfony\Component\Validator\ConstraintViolationList;

class ViolationNormalizerSpec extends ObjectBehavior
{
    function let(IdentifiableObjectRepositoryInterface $attributeRepository)
    {
        $this->beConstructedWith($attributeRepository);
    }

    function it_normalizes_an_exception()
    {
        $violationCode = new ConstraintViolation('Not Blank', '', [], '', 'code', '');
        $violationName = new ConstraintViolation('Too long', '', [], '', 'name', '');
        $constraintViolation = new ConstraintViolationList([$violationCode, $violationName]);
        $exception = new ViolationHttpException($constraintViolation);

        $this->normalize($exception)->shouldReturn([
            'code'    => Response::HTTP_UNPROCESSABLE_ENTITY,
            'message' => 'Validation failed.',
            'errors'  => [
                ['property' => 'code', 'message' => 'Not Blank'],
                ['property' => 'name', 'message' => 'Too long'],
            ],
        ]);
    }

    function it_normalizes_an_exception_with_error_on_product_identifier_when_blank(
        ViolationHttpException $exception,
        ConstraintViolationList $constraintViolations,
        ConstraintViolation $violation,
        EntityWithValuesInterface $product,
        \ArrayIterator $iterator,
        WriteValueCollection $values,
        ValueInterface $identifier,
        AttributeInterface $attribute,
        Constraint $constraint,
        $attributeRepository
    ) {
        $attribute->getType()->willReturn('pim_catalog_identifier');
        $attribute->getCode()->willReturn('identifier');

        $identifier->getAttributeCode()->willReturn('identifier');
        $attributeRepository->findOneByIdentifier('identifier')->willReturn($attribute);

        $product->getValues()->willReturn($values);
        $values->getByKey('sku')->willReturn($identifier);

        $violation->getRoot()->willReturn($product);
        $violation->getMessage()->willReturn('Not Blank');
        $violation->getPropertyPath()->willReturn('values[sku].text');
        $violation->getMessageTemplate()->willReturn(null);

        $constraintViolations->getIterator()->willReturn($iterator);
        $iterator->rewind()->shouldBeCalled();
        $valueCount = 1;
        $iterator->valid()->will(
            function () use (&$valueCount) {
                return $valueCount-- > 0;
            }
        );
        $iterator->current()->willReturn($violation);
        $iterator->next()->shouldBeCalled();

        $exception->getViolations()->willReturn($constraintViolations);
        $exception->getStatusCode()->willReturn(Response::HTTP_UNPROCESSABLE_ENTITY);

        $violation->getConstraint()->willReturn($constraint);

        $this->normalize($exception)->shouldReturn([
            'code'    => Response::HTTP_UNPROCESSABLE_ENTITY,
            'message' => '',
            'errors'  => [
                ['property' => 'identifier', 'message' => 'Not Blank'],
            ],
        ]);
    }

    function it_normalizes_an_exception_with_error_on_product_identifier_when_too_long(
        ViolationHttpException $exception,
        ConstraintViolationList $constraintViolations,
        ConstraintViolation $violationProductValue,
        EntityWithValuesInterface $product,
        \ArrayIterator $iterator,
        WriteValueCollection $productValues,
        ValueInterface $sku,
        AttributeInterface $attribute,
        Constraint $lengthConstraint,
        $attributeRepository
    ) {
        $attribute->getType()->willReturn('pim_catalog_identifier');
        $attribute->getCode()->willReturn('sku');
        $attribute->getMaxCharacters()->willReturn(10);

        $sku->getAttributeCode()->willReturn('identifier');
        $sku->getLocaleCode()->willReturn(null);
        $sku->getScopeCode()->willReturn(null);

        $attributeRepository->findOneByIdentifier('identifier')->willReturn($attribute);

        $product->getValues()->willReturn($productValues);
        $productValues->getByKey('sku')->willReturn($sku);

        $violationProductValue->getRoot()->willReturn($product);
        $violationProductValue->getMessage()->willReturn('Product value sku is too long (10)');
        $violationProductValue->getPropertyPath()->willReturn('values[sku].text');
        $violationProductValue->getConstraint()->willReturn($lengthConstraint);
        $violationProductValue->getMessageTemplate()->willReturn('This value is too long. It should have {{ limit }} character or less.|This value is too long. It should have {{ limit }} characters or less.');

        $constraintViolations->getIterator()->willReturn($iterator);
        $iterator->rewind()->shouldBeCalled();
        $valueCount = 1;
        $iterator->valid()->will(
            function () use (&$valueCount) {
                return $valueCount-- > 0;
            }
        );
        $iterator->current()->willReturn($violationProductValue);
        $iterator->next()->shouldBeCalled();

        $exception->getViolations()->willReturn($constraintViolations);
        $exception->getStatusCode()->willReturn(Response::HTTP_UNPROCESSABLE_ENTITY);

        $violationProductValue->getConstraint()->willReturn($lengthConstraint);

        $this->normalize($exception)->shouldReturn([
            'code'    => Response::HTTP_UNPROCESSABLE_ENTITY,
            'message' => '',
            'errors'  => [
                [
                    'property' => 'identifier',
                    'message'  => 'Product value sku is too long (10)',
                ],
            ],
        ]);
    }

    function it_normalizes_an_exception_with_error_on_product_identifier_when_regexp(
        ViolationHttpException $exception,
        ConstraintViolationList $constraintViolations,
        ConstraintViolation $violationIdentifier,
        ConstraintViolation $violationProductValue,
        EntityWithValuesInterface $product,
        \ArrayIterator $iterator,
        WriteValueCollection $productValues,
        ValueInterface $sku,
        AttributeInterface $attribute,
        Constraint $regexpConstraint,
        $attributeRepository
    ) {
        $attribute->getType()->willReturn('pim_catalog_identifier');
        $attribute->getCode()->willReturn('sku');
        $attribute->getMaxCharacters()->willReturn(10);

        $sku->getAttributeCode()->willReturn('sku');
        $sku->getLocaleCode()->willReturn(null);
        $sku->getScopeCode()->willReturn(null);

        $attributeRepository->findOneByIdentifier('sku')->willReturn($attribute);

        $product->getValues()->willReturn($productValues);
        $productValues->getByKey('sku')->willReturn($sku);

        $violationIdentifier->getRoot()->willReturn($product);
        $violationIdentifier->getMessage()->willReturn('This value is not valid.');
        $violationIdentifier->getPropertyPath()->willReturn('identifier');
        $violationIdentifier->getConstraint()->willReturn($regexpConstraint);
        $violationIdentifier->getMessageTemplate()->willReturn(null);

        $violationProductValue->getRoot()->willReturn($product);
        $violationProductValue->getMessage()->willReturn('This value is not valid.');
        $violationProductValue->getPropertyPath()->willReturn('values[sku].text');
        $violationProductValue->getConstraint()->willReturn($regexpConstraint);
        $violationProductValue->getMessageTemplate()->willReturn(null);

        $constraintViolations->getIterator()->willReturn($iterator);
        $iterator->rewind()->shouldBeCalled();
        $valueCount = 2;
        $iterator->valid()->will(
            function () use (&$valueCount) {
                return $valueCount-- > 0;
            }
        );
        $iterator->current()->willReturn($violationIdentifier, $violationProductValue);
        $iterator->next()->shouldBeCalled();

        $exception->getViolations()->willReturn($constraintViolations);
        $exception->getStatusCode()->willReturn(Response::HTTP_UNPROCESSABLE_ENTITY);

        $violationIdentifier->getConstraint()->willReturn($regexpConstraint);

        $this->normalize($exception)->shouldReturn([
            'code'    => Response::HTTP_UNPROCESSABLE_ENTITY,
            'message' => '',
            'errors'  => [
                [
                    'property' => 'identifier',
                    'message'  => 'This value is not valid.',
                ],
            ],
        ]);
    }

    function it_normalizes_an_exception_with_error_on_attribute_localizable_and_scopable(
        ViolationHttpException $exception,
        ConstraintViolationList $constraintViolations,
        ConstraintViolation $violation,
        EntityWithValuesInterface $product,
        \ArrayIterator $iterator,
        WriteValueCollection $productValues,
        ValueInterface $description,
        AttributeInterface $attribute,
        Constraint $constraint,
        $attributeRepository
    ) {
        $attribute->getType()->willReturn('pim_catalog_text');
        $attribute->getCode()->willReturn('description');
        $description->getAttributeCode()->willReturn('description');
        $description->getLocaleCode()->willReturn('en_US');
        $description->getScopeCode()->willReturn('ecommerce');

        $attributeRepository->findOneByIdentifier('description')->willReturn($attribute);

        $product->getValues()->willReturn($productValues);
        $productValues->getByKey('description-en_US-ecommerce')->willReturn($description);

        $violation->getRoot()->willReturn($product);
        $violation->getMessage()->willReturn('Not Blank');
        $violation->getPropertyPath()->willReturn('values[description-en_US-ecommerce].textarea');
        $violation->getMessageTemplate()->willReturn(null);

        $constraintViolations->getIterator()->willReturn($iterator);
        $iterator->rewind()->shouldBeCalled();
        $valueCount = 1;
        $iterator->valid()->will(
            function () use (&$valueCount) {
                return $valueCount-- > 0;
            }
        );
        $iterator->current()->willReturn($violation);
        $iterator->next()->shouldBeCalled();

        $exception->getViolations()->willReturn($constraintViolations);
        $exception->getStatusCode()->willReturn(Response::HTTP_UNPROCESSABLE_ENTITY);

        $violation->getConstraint()->willReturn($constraint);

        $this->normalize($exception)->shouldReturn([
            'code'    => Response::HTTP_UNPROCESSABLE_ENTITY,
            'message' => '',
            'errors'  => [
                [
                    'property'  => 'values',
                    'message'   => 'Not Blank',
                    'attribute' => 'description',
                    'locale'    => 'en_US',
                    'scope'     => 'ecommerce',
                ],
            ],
        ]);
    }

    function it_normalizes_an_exception_using_constraint_constraint_payload_instead_of_property_path(
        ViolationHttpException $exception,
        ConstraintViolationList $constraintViolations,
        ConstraintViolation $violation,
        \ArrayIterator $iterator,
        AttributeInterface $attribute,
        Constraint $constraint
    ) {
        $violation->getRoot()->willReturn($attribute);
        $violation->getMessage()->willReturn('The locale "ab_CD" does not exist.');
        $violation->getPropertyPath()->willReturn('translations[0].locale');
        $violation->getMessageTemplate()->willReturn(null);

        $constraintViolations->getIterator()->willReturn($iterator);
        $iterator->rewind()->shouldBeCalled();
        $valueCount = 1;
        $iterator->valid()->will(
            function () use (&$valueCount) {
                return $valueCount-- > 0;
            }
        );
        $iterator->current()->willReturn($violation);
        $iterator->next()->shouldBeCalled();

        $exception->getViolations()->willReturn($constraintViolations);
        $exception->getStatusCode()->willReturn(Response::HTTP_UNPROCESSABLE_ENTITY);

        $violation->getConstraint()->willReturn($constraint);
        $constraint->payload = ['standardPropertyName' => 'labels'];

        $this->normalize($exception)->shouldReturn([
            'code'    => Response::HTTP_UNPROCESSABLE_ENTITY,
            'message' => '',
            'errors'  => [
                [
                    'property' => 'labels',
                    'message'  => 'The locale "ab_CD" does not exist.',
                ],
            ],
        ]);
    }
}
