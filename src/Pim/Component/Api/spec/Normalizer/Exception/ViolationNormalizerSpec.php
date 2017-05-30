<?php

namespace spec\Pim\Component\Api\Normalizer\Exception;

use PhpSpec\ObjectBehavior;
use Pim\Component\Api\Exception\ViolationHttpException;
use Pim\Component\Catalog\Model\AttributeInterface;
use Pim\Component\Catalog\Model\ProductInterface;
use Pim\Component\Catalog\Model\ProductValueInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Validator\Constraint;
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
                ['property' => 'name', 'message' => 'Too long']
            ]
        ]);
    }

    function it_normalizes_an_exception_with_error_on_product_identifier(
        ViolationHttpException $exception,
        ConstraintViolationList $constraintViolations,
        ConstraintViolation $violation,
        ProductInterface $product,
        \ArrayIterator $iterator,
        \ArrayIterator $productValues,
        ProductValueInterface $identifier,
        AttributeInterface $attribute,
        Constraint $constraint
    ) {
        $attribute->getAttributeType()->willReturn('pim_catalog_identifier');
        $attribute->getCode()->willReturn('identifier');
        $identifier->getAttribute()->willReturn($attribute);
        $product->getValues()->willReturn($productValues);
        $productValues->rewind()->willReturn($identifier);
        $valueCount = 1;
        $productValues->valid()->will(
            function () use (&$valueCount) {
                return $valueCount-- > 0;
            }
        );
        $productValues->current()->willReturn($identifier);
        $productValues->offsetGet('sku')->willReturn($identifier);
        $productValues->next()->willReturn(null);

        $violation->getRoot()->willReturn($product);
        $violation->getMessage()->willReturn('Not Blank');
        $violation->getPropertyPath()->willReturn('values[sku].varchar');

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
            ]
        ]);
    }

    function it_normalizes_an_exception_with_error_on_attribute_localizable_and_scopable(
        ViolationHttpException $exception,
        ConstraintViolationList $constraintViolations,
        ConstraintViolation $violation,
        ProductInterface $product,
        \ArrayIterator $iterator,
        \ArrayIterator $productValues,
        ProductValueInterface $description,
        AttributeInterface $attribute,
        Constraint $constraint
    ) {
        $attribute->getAttributeType()->willReturn('pim_catalog_text');
        $attribute->getCode()->willReturn('description');
        $description->getAttribute()->willReturn($attribute);
        $description->getLocale()->willReturn('en_US');
        $description->getScope()->willReturn('ecommerce');
        $product->getValues()->willReturn($productValues);
        $productValues->rewind()->willReturn($description);
        $valueCount = 1;
        $productValues->valid()->will(
            function () use (&$valueCount) {
                return $valueCount-- > 0;
            }
        );
        $productValues->current()->willReturn($description);
        $productValues->offsetGet('description-en_US-ecommerce')->willReturn($description);
        $productValues->next()->willReturn(null);

        $violation->getRoot()->willReturn($product);
        $violation->getMessage()->willReturn('Not Blank');
        $violation->getPropertyPath()->willReturn('values[description-en_US-ecommerce].varchar');

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
                    'scope'     => 'ecommerce'
                ],
            ]
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
                    'property'  => 'labels',
                    'message'   => 'The locale "ab_CD" does not exist.',
                ],
            ]
        ]);
    }
}
