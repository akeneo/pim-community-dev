<?php

namespace spec\Pim\Component\Api\Normalizer\Exception;

use PhpSpec\ObjectBehavior;
use Pim\Component\Api\Exception\ViolationHttpException;
use Pim\Component\Catalog\Model\AttributeInterface;
use Pim\Component\Catalog\Model\ProductInterface;
use Pim\Component\Catalog\Model\ProductValueInterface;
use Symfony\Component\HttpFoundation\Response;
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
                ['field' => 'code', 'message' => 'Not Blank'],
                ['field' => 'name', 'message' => 'Too long']
            ]
        ]);
    }

    function it_normalizes_an_exception_with_error_on_product_identifier(
        ViolationHttpException $exception,
        ConstraintViolationList $constraintViolation,
        ConstraintViolation $violationCode,
        ProductInterface $product,
        \ArrayIterator $iterator,
        \ArrayIterator $productValues,
        ProductValueInterface $identifier,
        AttributeInterface $attribute
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

        $violationCode->getRoot()->willReturn($product);
        $violationCode->getMessage()->willReturn('Not Blank');
        $violationCode->getPropertyPath()->willReturn('values[sku].varchar');

        $constraintViolation->getIterator()->willReturn($iterator);
        $iterator->rewind()->willReturn($violationCode);
        $valueCount = 1;
        $iterator->valid()->will(
            function () use (&$valueCount) {
                return $valueCount-- > 0;
            }
        );
        $iterator->current()->willReturn($violationCode);
        $iterator->next()->willReturn(null);

        $exception->getViolations()->willReturn($constraintViolation);
        $exception->getStatusCode()->willReturn(422);

        $this->normalize($exception)->shouldReturn([
            'code'    => Response::HTTP_UNPROCESSABLE_ENTITY,
            'message' => '',
            'errors'  => [
                ['field' => 'identifier', 'message' => 'Not Blank'],
            ]
        ]);
    }

    function it_normalizes_an_exception_with_error_on_attribute_localizable_and_scopable(
        ViolationHttpException $exception,
        ConstraintViolationList $constraintViolation,
        ConstraintViolation $violationDescription,
        ProductInterface $product,
        \ArrayIterator $iterator,
        \ArrayIterator $productValues,
        ProductValueInterface $description,
        AttributeInterface $attribute
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

        $violationDescription->getRoot()->willReturn($product);
        $violationDescription->getMessage()->willReturn('Not Blank');
        $violationDescription->getPropertyPath()->willReturn('values[description-en_US-ecommerce].varchar');

        $constraintViolation->getIterator()->willReturn($iterator);
        $iterator->rewind()->willReturn($violationDescription);
        $valueCount = 1;
        $iterator->valid()->will(
            function () use (&$valueCount) {
                return $valueCount-- > 0;
            }
        );
        $iterator->current()->willReturn($violationDescription);
        $iterator->next()->willReturn(null);

        $exception->getViolations()->willReturn($constraintViolation);
        $exception->getStatusCode()->willReturn(422);

        $this->normalize($exception)->shouldReturn([
            'code'    => Response::HTTP_UNPROCESSABLE_ENTITY,
            'message' => '',
            'errors'  => [
                [
                    'field'     => 'values',
                    'message'   => 'Not Blank',
                    'attribute' => 'description',
                    'locale'    => 'en_US',
                    'scope'     => 'ecommerce'
                ],
            ]
        ]);
    }
}
