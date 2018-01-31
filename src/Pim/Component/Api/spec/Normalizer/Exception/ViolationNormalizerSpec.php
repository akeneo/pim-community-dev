<?php

namespace spec\Pim\Component\Api\Normalizer\Exception;

use PhpSpec\ObjectBehavior;
use Pim\Component\Api\Exception\ViolationHttpException;
use Pim\Component\Catalog\Model\AttributeInterface;
use Pim\Component\Catalog\Model\EntityWithValuesInterface;
use Pim\Component\Catalog\Model\ProductInterface;
use Pim\Component\Catalog\Model\ValueCollectionInterface;
use Pim\Component\Catalog\Model\ValueInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\ConstraintViolation;
use Symfony\Component\Validator\ConstraintViolationList;

class ViolationNormalizerSpec extends ObjectBehavior
{
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
        ValueCollectionInterface $values,
        ValueInterface $identifier,
        AttributeInterface $attribute,
        Constraint $constraint
    ) {
        $attribute->getType()->willReturn('pim_catalog_identifier');
        $attribute->getCode()->willReturn('identifier');
        $identifier->getAttribute()->willReturn($attribute);
        $product->getValues()->willReturn($values);
        $values->getByKey('sku')->willReturn($identifier);

        $violation->getRoot()->willReturn($product);
        $violation->getMessage()->willReturn('Not Blank');
        $violation->getPropertyPath()->willReturn('values[sku].text');
        $violation->getMessageTemplate()->willReturn(null);

        $constraintViolations->getIterator()->willReturn($iterator);
        $iterator->rewind()->willReturn($violation);
        $valueCount = 1;
        $iterator->valid()->will(
            function () use (&$valueCount) {
                return $valueCount-- > 0;
            }
        );
        $iterator->current()->willReturn($violation);
        $iterator->next()->willReturn(null);

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
        ValueCollectionInterface $productValues,
        ValueInterface $sku,
        AttributeInterface $attribute,
        Constraint $lengthConstraint
    ) {
        $attribute->getType()->willReturn('pim_catalog_identifier');
        $attribute->getCode()->willReturn('sku');
        $attribute->getMaxCharacters()->willReturn(10);

        $sku->getAttribute()->willReturn($attribute);
        $sku->getLocale()->willReturn(null);
        $sku->getScope()->willReturn(null);
        $product->getValues()->willReturn($productValues);
        $productValues->getByKey('sku')->willReturn($sku);

        $violationProductValue->getRoot()->willReturn($product);
        $violationProductValue->getMessage()->willReturn('Product value sku is too long (10)');
        $violationProductValue->getPropertyPath()->willReturn('values[sku].text');
        $violationProductValue->getConstraint()->willReturn($lengthConstraint);
        $violationProductValue->getMessageTemplate()->willReturn('This value is too long. It should have {{ limit }} character or less.|This value is too long. It should have {{ limit }} characters or less.');

        $constraintViolations->getIterator()->willReturn($iterator);
        $iterator->rewind()->willReturn($violationProductValue);
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
        ValueCollectionInterface $productValues,
        ValueInterface $sku,
        AttributeInterface $attribute,
        Constraint $regexpConstraint
    ) {
        $attribute->getType()->willReturn('pim_catalog_identifier');
        $attribute->getCode()->willReturn('sku');
        $attribute->getMaxCharacters()->willReturn(10);

        $sku->getAttribute()->willReturn($attribute);
        $sku->getLocale()->willReturn(null);
        $sku->getScope()->willReturn(null);
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
        $iterator->rewind()->willReturn($violationIdentifier);
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
        ValueCollectionInterface $productValues,
        ValueInterface $description,
        AttributeInterface $attribute,
        Constraint $constraint
    ) {
        $attribute->getType()->willReturn('pim_catalog_text');
        $attribute->getCode()->willReturn('description');
        $description->getAttribute()->willReturn($attribute);
        $description->getLocale()->willReturn('en_US');
        $description->getScope()->willReturn('ecommerce');
        $product->getValues()->willReturn($productValues);
        $productValues->getByKey('description-en_US-ecommerce')->willReturn($description);

        $violation->getRoot()->willReturn($product);
        $violation->getMessage()->willReturn('Not Blank');
        $violation->getPropertyPath()->willReturn('values[description-en_US-ecommerce].textarea');
        $violation->getMessageTemplate()->willReturn(null);

        $constraintViolations->getIterator()->willReturn($iterator);
        $iterator->rewind()->willReturn($violation);
        $valueCount = 1;
        $iterator->valid()->will(
            function () use (&$valueCount) {
                return $valueCount-- > 0;
            }
        );
        $iterator->current()->willReturn($violation);
        $iterator->next()->willReturn(null);

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
        $iterator->rewind()->willReturn($violation);
        $valueCount = 1;
        $iterator->valid()->will(
            function () use (&$valueCount) {
                return $valueCount-- > 0;
            }
        );
        $iterator->current()->willReturn($violation);
        $iterator->next()->willReturn(null);

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
