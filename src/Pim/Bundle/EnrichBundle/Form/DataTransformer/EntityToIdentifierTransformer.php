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

    /** @var boolean */
    protected $multiple;

    /**
     * Delimiter used by imploded array
     * @var string
     */
    protected $delimiter;

    /** @var PropertyAccessorInterface */
    protected $propertyAccessor;

    /**
     * Constructor
     *
     * @param ObjectRepository          $repository
     * @param boolean                   $multiple
     * @param PropertyAccessorInterface $propertyAccessor
     * @param string                    $delimiter
     */
    public function __construct(
        ObjectRepository $repository,
        $multiple,
        PropertyAccessorInterface $propertyAccessor = null,
        $delimiter = ','
    ) {
        $this->repository       = $repository;
        $this->multiple         = $multiple;
        $this->propertyAccessor = $propertyAccessor ?: PropertyAccess::createPropertyAccessor();
        $this->delimiter        = $delimiter;
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

            $ids = array_map(
                function ($val) {
                    return $this->propertyAccessor->getValue($val, 'id');
                },
                $value
            );

            if (null !== $this->delimiter) {
                return implode($this->delimiter, $ids);
            }

            return $ids;
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
            if (is_string($value) && (null !== $this->delimiter)) {
                $value = explode($this->delimiter, $value);
            }
            if (!is_array($value)) {
                throw new UnexpectedTypeException($value, 'array');
            }
            if (method_exists($this->repository, 'findByIds')) {
                return $this->repository->findByIds($value);
            } else {
                return $this->repository->findBy(['id' => $value]);
            }
        }

        return $this->repository->find($value);
    }
}
