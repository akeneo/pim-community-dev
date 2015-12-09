<?php

namespace Pim\Bundle\EnrichBundle\Form\DataTransformer;

use Akeneo\Component\StorageUtils\Repository\IdentifiableObjectRepositoryInterface;
use Doctrine\Common\Util\ClassUtils;
use Symfony\Component\Form\DataTransformerInterface;

/**
 * Transformer for identifiable object.
 * Transform a object to its code and retrieve an object with its code.
 *
 * @author    JM Leroux <jean-marie.leroux@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class IdentifiableModelTransformer implements DataTransformerInterface
{
    /** @var IdentifiableObjectRepositoryInterface */
    protected $repository;

    /** @var array */
    protected $options;

    /**
     * @param IdentifiableObjectRepositoryInterface $repository
     */
    public function __construct(IdentifiableObjectRepositoryInterface $repository)
    {
        $this->repository = $repository;
    }

    /**
     * {@inheritdoc}
     */
    public function reverseTransform($identifier)
    {
        return null === $identifier ?: $this->repository->findOneByIdentifier($identifier);
    }

    /**
     * {@inheritdoc}
     */
    public function transform($model)
    {
        return $model;
    }
}
