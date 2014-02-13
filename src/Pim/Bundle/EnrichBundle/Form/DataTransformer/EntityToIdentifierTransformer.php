<?php

namespace Pim\Bundle\EnrichBundle\Form\DataTransformer;

use Symfony\Component\Form\DataTransformerInterface;
use Symfony\Component\PropertyAccess\PropertyAccessorInterface;
use Doctrine\Common\Persistence\ObjectRepository;
use Symfony\Component\PropertyAccess\PropertyAccess;
use Symfony\Component\Form\Exception\UnexpectedTypeException;

/**
 * Transforms entity into its identifier and the opposite
 *
 * @author    Gildas Quemener <gildas@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class EntityToIdentifierTransformer implements DataTransformerInterface
{
    /** @var ObjectRepository */
    protected $repository;

    /** @var Boolean */
    protected $multiple;

    /** @var PropertyAccessorInterface */
    protected $propertyAccessor;

    /**
     * Constructor
     *
     * @param ObjectRepository          $repository
     * @param Boolean                   $multiple
     * @param PropertyAccessorInterface $propertyAccessor
     */
    public function __construct(
        ObjectRepository $repository,
        $multiple,
        PropertyAccessorInterface $propertyAccessor = null
    ) {
        $this->repository       = $repository;
        $this->multiple         = $multiple;
        $this->propertyAccessor = $propertyAccessor ?: PropertyAccess::createPropertyAccessor();
    }

    /**
     * {@inheritdoc}
     */
    public function transform($value)
    {
        if (null === $value) {
            return null;
        }

        if ($this->multiple) {
            if (!is_array($value)) {
                throw new UnexpectedTypeException($value, 'array');
            }

            return array_map(
                function ($val) {
                    return $this->propertyAccessor->getValue($val, 'id');
                },
                $value
            );
        }

        if (!is_object($value)) {
            throw new UnexpectedTypeException($value, 'object');
        }

        return $this->propertyAccessor->getValue($value, 'id');
    }

    /**
     * {@inheritdoc}
     */
    public function reverseTransform($value)
    {
        if ($this->multiple) {
            if (!is_array($value)) {
                throw new UnexpectedTypeException($value, 'array');
            }

            return $this->repository->findBy(['id' => $value]);
        }

        return $this->repository->find($value);
    }
}
