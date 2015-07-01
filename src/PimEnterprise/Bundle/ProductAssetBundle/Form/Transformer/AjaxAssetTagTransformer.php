<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2015 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PimEnterprise\Bundle\ProductAssetBundle\Form\Transformer;

use Akeneo\Component\StorageUtils\Repository\IdentifiableObjectRepositoryInterface;
use Akeneo\Component\StorageUtils\Saver\SaverInterface;
use Symfony\Component\Form\DataTransformerInterface;

/**
 * Ajax asset tag transformer
 *
 * @author Willy Mesnage <willy.mesnage@akeneo.com>
 */
class AjaxAssetTagTransformer implements DataTransformerInterface
{
    /** @var SaverInterface */
    protected $saver;

    /** @var IdentifiableObjectRepositoryInterface */
    protected $repository;

    /** @var array */
    protected $options;

    /**
     * @param SaverInterface                        $saver
     * @param IdentifiableObjectRepositoryInterface $repository
     * @param array                                 $options
     */
    public function __construct(
        SaverInterface $saver,
        IdentifiableObjectRepositoryInterface $repository,
        array $options
    ) {
        $this->saver      = $saver;
        $this->repository = $repository;
        $this->options    = $options;
    }

    /**
     * {@inheritdoc}
     */
    public function reverseTransform($value)
    {
        if (null === $value || '' === $value) {
            return [];
        }

        if ($this->options['multiple']) {
            return $this->reverseTransformMultiple($value);
        }

        return $this->reverseTransformSimple($value);
    }

    /**
     * {@inheritdoc}
     */
    public function transform($value)
    {
        if (null === $value) {
            return '';
        }

        if ($this->options['multiple']) {
            $values = [];
            foreach ($value as $entity) {
                $values[] = (null !== $entity) ? (string) $entity : '';
            }

            return implode(',', $values);
        }

        return (string) $value;
    }

    /**
     * returns the labels corresponding to the given value
     *
     * @param mixed $value
     *
     * @return array
     */
    public function getOptions($value)
    {
        if ($this->options['multiple']) {
            $options = [];

            foreach ($value as $entity) {
                $options[] = $this->getOption($entity);
            }

            return $options;
        } else {
            return $value
                ? $this->getOption($value)
                : null;
        }
    }

    /**
     * @param string $values
     *
     * @return object[]
     */
    protected function reverseTransformMultiple($values)
    {
        $transformedValues = [];
        foreach (explode(',', $values) as $identifier) {
            $transformedValues[] = $this->reverseTransformSimple($identifier);
        }

        return $transformedValues;
    }

    /**
     * @param string $value
     *
     * @throws \LogicException
     *
     * @return object
     *
     */
    protected function reverseTransformSimple($value)
    {
        $transformedValue = $this->repository->findOneByIdentifier($value);

        if (null === $transformedValue) {
            $entity = new $this->options['class']();
            $identifierProperties = $this->repository->getIdentifierProperties();
            if (1 === count($identifierProperties)) {
                $setMethod = sprintf('set%s', ucfirst($identifierProperties[0]));
            } else {
                throw new \LogicException(
                    sprintf(
                        'Repository "%s" has too much identifier properties to automatically create "%s" entity',
                        get_class($this->repository),
                        $this->options['class']
                    )
                );
            }

            $entity->$setMethod($value);
            $this->saver->save($entity);
            $transformedValue = $entity;
        }

        return $transformedValue;
    }

    /**
     * Sets the choices representing an entity in an array
     *
     * @param object $entity
     *
     * @return array
     */
    protected function getOption($entity)
    {
        return [
            'id'   => (string) $entity,
            'text' => (string) $entity
        ];
    }
}
