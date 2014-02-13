<?php

namespace Pim\Bundle\EnrichBundle\Form\DataTransformer;

use Symfony\Component\Form\DataTransformerInterface;
use Symfony\Component\PropertyAccess\PropertyAccessorInterface;
use Doctrine\Common\Persistence\ObjectRepository;
use Symfony\Component\PropertyAccess\PropertyAccess;

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

    /** @var PropertyAccessorInterface */
    protected $propertyAccessor;

    /**
     * Constructor
     *
     * @param ObjectRepository          $repository
     * @param PropertyAccessorInterface $propertyAccessor
     */
    public function __construct(ObjectRepository $repository, PropertyAccessorInterface $propertyAccessor = null)
    {
        $this->repository       = $repository;
        $this->propertyAccessor = $propertyAccessor ?: PropertyAccess::createPropertyAccessor();
    }

    /**
     * {@inheritdoc}
     */
    public function transform($value)
    {
        return $this->propertyAccessor->getValue($value, 'id');
    }

    /**
     * {@inheritdoc}
     */
    public function reverseTransform($value)
    {
        return $this->repository->find($value);
    }
}
