<?php

namespace Akeneo\Platform\Bundle\UIBundle\Form\Transformer;

use Akeneo\Tool\Component\StorageUtils\Repository\IdentifiableObjectRepositoryInterface;
use Symfony\Component\Form\DataTransformerInterface;
use Symfony\Component\Form\Exception\UnexpectedTypeException;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\PropertyAccess\PropertyAccess;

/**
 * Transformer for identifiable object.
 * Transform a object to its identifier value and retrieve an object with its identifier.
 *
 * @author    JM Leroux <jean-marie.leroux@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class IdentifiableModelTransformer implements DataTransformerInterface
{
    /** @var IdentifiableObjectRepositoryInterface */
    protected $repository;

    /** @var bool */
    protected $multiple;

    /**
     * @param IdentifiableObjectRepositoryInterface $repository
     * @param array                                 $options
     */
    public function __construct(IdentifiableObjectRepositoryInterface $repository, array $options)
    {
        $this->repository = $repository;
        $resolver = new OptionsResolver();
        $this->configureOptions($resolver);
        $this->multiple = $resolver->resolve($options)['multiple'];
    }

    /**
     * {@inheritdoc}
     */
    public function reverseTransform($identifier)
    {
        if (null === $identifier) {
            return null;
        }

        if (true === $this->multiple) {
            if (!is_array($identifier)) {
                throw new UnexpectedTypeException($identifier, 'array');
            }
            $models = [];
            foreach ($identifier as $scalarIdentifier) {
                $models[] = $this->repository->findOneByIdentifier($scalarIdentifier);
            }

            return $models;
        }

        return $this->repository->findOneByIdentifier($identifier);
    }

    /**
     * {@inheritdoc}
     */
    public function transform($model)
    {
        if (null === $model) {
            return null;
        }

        if (count($this->repository->getIdentifierProperties()) > 1) {
            throw new \InvalidArgumentException('Cannot transform object with multiple identifiers');
        }

        $identifierProperty = $this->repository->getIdentifierProperties()[0];
        $propertyAccessor = PropertyAccess::createPropertyAccessor();

        if ($this->multiple) {
            if (!is_array($model)) {
                throw new UnexpectedTypeException($model, 'array');
            }

            $identifiers = [];
            foreach ($model as $object) {
                $identifiers[] = $propertyAccessor->getValue($object, $identifierProperty);
            }

            return $identifiers;
        }

        if (!is_object($model)) {
            throw new UnexpectedTypeException($model, 'object');
        }

        return $propertyAccessor->getValue($model, $identifierProperty);
    }

    protected function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'multiple' => false,
        ]);
        $resolver->setAllowedTypes('multiple', ['bool']);
    }
}
