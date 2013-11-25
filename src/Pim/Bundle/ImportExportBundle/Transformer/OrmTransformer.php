<?php

namespace Pim\Bundle\ImportExportBundle\Transformer;

use Symfony\Bridge\Doctrine\RegistryInterface;
use Symfony\Component\PropertyAccess\PropertyAccessorInterface;
use Pim\Bundle\ImportExportBundle\Transformer\Guesser\GuesserInterface;
use Pim\Bundle\ImportExportBundle\Transformer\Property\EntityUpdaterInterface;

/**
 * Description of OrmTransformer
 *
 * @author    Antoine Guigan <antoine@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class OrmTransformer
{
    /**
     * @var RegistryInterface
     */
    protected $doctrine;

    /**
     * @var PropertyAccessorInterface
     */
    protected $propertyAccessor;

    /**
     * @var GuesserInterface
     */
    protected $guesser;

    /**
     * @var LabelTransformerInterface
     */
    protected $labelTransformer;

    /**
     * @var array
     */
    protected $transformers = array();

    /**
     * @var array
     */
    protected $columnInfos = array();

    /**
     * Constructor
     *
     * @param RegistryInterface         $doctrine
     * @param PropertyAccessorInterface $propertyAccessor
     * @param GuesserInterface          $guesser
     * @param LabelTransformerInterface $labelTransformer
     */
    public function __construct(
        RegistryInterface $doctrine,
        PropertyAccessorInterface $propertyAccessor,
        GuesserInterface $guesser,
        LabelTransformerInterface $labelTransformer
    ) {
        $this->doctrine = $doctrine;
        $this->propertyAccessor = $propertyAccessor;
        $this->guesser = $guesser;
        $this->labelTransformer = $labelTransformer;
    }

    /**
     * Transforms an array into an entity
     *
     * @param string $class
     * @param array  $data
     *
     * @return object
     */
    public function transform($class, array $data)
    {
        $entity = $this->getEntity($class, $data);
        foreach ($data as $label => $value) {
            $this->setProperty($class, $entity, $label, $value);
        }

        return $entity;
    }

    /**
     * Sets a property of the object
     *
     * @param string $class
     * @param object $entity
     * @param string $label
     * @param mixed  $value
     */
    protected function setProperty($class, $entity, $label, $value)
    {
        $columnInfo = $this->getColumnInfo($class, $label);
        list($transformer, $options) = $this->getTransformerInfo($class, $columnInfo);

        $value = $transformer->transform($value, $options);

        if ($transformer instanceof EntityUpdaterInterface) {
            $transformer->setValue($entity, $columnInfo, $value, $options);
        } else {
            $this->propertyAccessor->setValue($entity, $columnInfo['propertyPath'], $value);
        }
    }

    /**
     * Finds or creates an entity for given class and data
     *
     * @param string $class
     * @param array  $data
     *
     * @return object
     */
    protected function getEntity($class, array $data)
    {
        $object = $this->doctrine->getRepository($class)->findOneBy(array('code' => $data['code']));
        if (!$object) {
            $object = $this->create($class);
        }

        return $object;
    }

    /**
     * Creates an entity of the given class
     *
     * @param  string $class
     * @return object
     */
    protected function create($class)
    {
        return new $class;
    }

    /**
     * Returns the transformer info for a column
     *
     * @param string $class
     * @param array  $columnInfo
     *
     * @throws \RuntimeException
     * @return array
     */
    protected function getTransformerInfo($class, $columnInfo)
    {
        $label = $columnInfo['label'];
        if (!isset($this->transformers[$class][$label])) {
            if (!isset($this->transformers[$class])) {
                $this->transformers[$class] = array();
            }
            $this->transformers[$class][$label] = $this->guesser->getTransformerInfo(
                $columnInfo,
                $this->doctrine->getManager()->getClassMetadata($class)
            );

            if (!$this->transformers[$class][$label]) {
                throw new \RuntimeException(sprintf('Property path %s not configured for class %s', $label, $class));
            }
        }

        return $this->transformers[$class][$label];
    }

    /**
     * Returns the information for a column label
     *
     * @param string $class
     * @param string $label
     *
     * @return array
     */
    protected function getColumnInfo($class, $label)
    {
        if (!isset($this->columnInfos[$class][$label])) {
            if (!isset($this->columnInfos[$class])) {
                $this->columnInfos[$class] = array();
            }
            $this->columnInfos[$class][$label] = $this->labelTransformer->transform($label);
        }

        return $this->columnInfos[$class][$label];
    }
}
