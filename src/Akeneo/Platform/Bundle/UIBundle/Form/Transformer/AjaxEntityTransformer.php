<?php

namespace Akeneo\Platform\Bundle\UIBundle\Form\Transformer;

use Akeneo\Pim\Structure\Bundle\Doctrine\ORM\Repository\AttributeOptionRepository;
use Symfony\Component\Form\DataTransformerInterface;

/**
 * Ajax choice transformer
 *
 * @author    Antoine Guigan <antoine@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class AjaxEntityTransformer implements DataTransformerInterface
{
    /**
     * @var AttributeOptionRepository
     */
    protected $repository;

    /**
     * @var array
     */
    protected $options;

    /**
     * Constructor
     *
     * @param AttributeOptionRepository $repository
     * @param array                     $options
     */
    public function __construct(AttributeOptionRepository $repository, array $options)
    {
        $this->repository = $repository;
        $this->options = $options;
    }

    /**
     * {@inheritdoc}
     */
    public function reverseTransform($value)
    {
        if ($this->options['multiple']) {
            if (!$value) {
                return [];
            }

            $values = [];
            foreach (explode(',', $value) as $id) {
                $values[] = $this->repository->getOption($id, $this->options['collection_id']);
            }

            return $values;
        } else {
            return $value ? $this->repository->getOption($value, $this->options['collection_id']) : null;
        }
    }

    /**
     * {@inheritdoc}
     */
    public function transform($value)
    {
        if ($this->options['multiple']) {
            $values = [];
            foreach ($value as $entity) {
                $values[] = $this->repository->getOptionId($entity);
            }

            return implode(',', $values);
        } else {
            return $value ? $this->repository->getOptionId($value) : '';
        }
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
        } elseif ($value) {
            return $value
                ? $this->getOption($value)
                : null;
        }
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
            'id'    => $this->repository->getOptionId($entity),
            'text'  => $this->repository->getOptionLabel($entity, $this->options['locale'])
        ];
    }

    /**
     * Returns an entity for a value
     *
     * @param mixed $id
     *
     * @return object
     */
    protected function getEntity($id)
    {
        return $this->repository->getOption($id, $this->options['collection_id'], $this->options);
    }
}
