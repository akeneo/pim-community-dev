<?php

namespace Pim\Bundle\EnrichBundle\Form\DataTransformer;

use Doctrine\Common\Persistence\ObjectRepository;
use Symfony\Component\Form\DataTransformerInterface;
use Symfony\Component\Form\Exception\UnexpectedTypeException;
use Symfony\Component\PropertyAccess\PropertyAccess;
use Symfony\Component\PropertyAccess\PropertyAccessorInterface;

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

    /** @var bool */
    protected $multiple;

    /**
     * Delimiter used by imploded array
     *
     * @var string
     */
    protected $delimiter;

    /** @var PropertyAccessorInterface */
    protected $propertyAccessor;

    /** @var string */
    protected $identifierProperty;

    /**
     * Constructor
     *
     * @param ObjectRepository          $repository
     * @param bool                      $multiple
     * @param PropertyAccessorInterface $propertyAccessor
     * @param string                    $delimiter
     * @param string                    $identifierProperty
     */
    public function __construct(
        ObjectRepository $repository,
        $multiple,
        PropertyAccessorInterface $propertyAccessor = null,
        $delimiter = ',',
        $identifierProperty = 'id'
    ) {
        $this->repository = $repository;
        $this->multiple = $multiple;
        $this->propertyAccessor = $propertyAccessor ?: PropertyAccess::createPropertyAccessor();
        $this->delimiter = $delimiter;
        $this->identifierProperty = $identifierProperty;
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

            $identifiers = array_map(function ($value) {
                return $this->propertyAccessor->getValue($value, $this->identifierProperty);
            }, $value);

            if (null !== $this->delimiter) {
                $identifiers = implode($this->delimiter, $identifiers);
            }

            return $identifiers;
        }

        if (!is_object($value)) {
            throw new UnexpectedTypeException($value, 'object');
        }

        return $this->propertyAccessor->getValue($value, $this->identifierProperty);
    }

    /**
     * {@inheritdoc}
     */
    public function reverseTransform($value)
    {
        if (null === $value) {
            return null;
        }

        if ($this->multiple) {
            if (is_string($value) && (null !== $this->delimiter)) {
                $value = explode($this->delimiter, $value);
            }
            if (!is_array($value)) {
                throw new UnexpectedTypeException($value, 'array');
            }
            // PIM-5976: in GroupType, we use the pim_object_identifier field type which rely on product repository,
            // the findBy(['id' => [1, 2]) does not work properly with Mongodb, the following patch workaround this
            // Ideally, we should revamp the group management screen to use the same stack than the variant group and
            // avoid to use this transformer
            if ('id' === $this->identifierProperty && method_exists($this->repository, 'findByIds')) {
                return $this->repository->findByIds($value);
            } else {
                return $this->repository->findBy([$this->identifierProperty => $value]);
            }
        }

        return $this->repository->findOneBy([$this->identifierProperty => $value]);
    }
}
