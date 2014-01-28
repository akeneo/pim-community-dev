<?php

namespace Pim\Bundle\TransformBundle\Transformer;

use Symfony\Bridge\Doctrine\RegistryInterface;
use Symfony\Component\PropertyAccess\PropertyAccessorInterface;
use Pim\Bundle\TransformBundle\Transformer\ColumnInfo\ColumnInfoTransformerInterface;
use Pim\Bundle\TransformBundle\Transformer\Guesser\GuesserInterface;

/**
 * Transformer for nested entities
 *
 * @author    Antoine Guigan <antoine@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class NestedEntityTransformer extends EntityTransformer
{
    /**
     * @var EntityTransformerInterface
     */
    protected $transformerRegistry;

    /**
     * Constructor
     *
     * @param RegistryInterface              $doctrine
     * @param PropertyAccessorInterface      $propertyAccessor
     * @param GuesserInterface               $guesser
     * @param ColumnInfoTransformerInterface $columnInfoTransformer
     * @param EntityTransformerInterface     $transformerRegistry
     */
    public function __construct(
        RegistryInterface $doctrine,
        PropertyAccessorInterface $propertyAccessor,
        GuesserInterface $guesser,
        ColumnInfoTransformerInterface $columnInfoTransformer,
        EntityTransformerInterface $transformerRegistry
    ) {
        parent::__construct($doctrine, $propertyAccessor, $guesser, $columnInfoTransformer);
        $this->transformerRegistry = $transformerRegistry;
    }

    /**
     * Transforms a nested entity
     *
     * @param string $class
     * @param string $propertyPath
     * @param string $nestedClass
     * @param array  $data
     *
     * @return object
     */
    protected function transformNestedEntity($class, $propertyPath, $nestedClass, array $data)
    {
        $entity = $this->transformerRegistry->transform($nestedClass, $data);
        $this->addNestedErrors($class, $propertyPath, $nestedClass);

        return $entity;
    }

    /**
     * Adds nested errors to the current errors
     *
     * @param string $class
     * @param string $propertyPath
     * @param string $nestedClass
     */
    protected function addNestedErrors($class, $propertyPath, $nestedClass)
    {
        $errors = $this->transformerRegistry->getErrors($nestedClass);
        if (!count($errors)) {
            return;
        }
        if (!isset($this->errors[$class][$propertyPath])) {
            $this->errors[$class][$propertyPath] = array();
        }
        foreach ($errors as $fieldErrors) {
            $this->errors[$class][$propertyPath] = array_merge($this->errors[$class][$propertyPath], $fieldErrors);
        }
    }
}
