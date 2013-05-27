<?php

namespace Oro\Bundle\GridBundle\Sorter\ORM;

use Oro\Bundle\GridBundle\Sorter\SorterInterface;
use Oro\Bundle\GridBundle\Datagrid\ProxyQueryInterface;
use Oro\Bundle\GridBundle\Field\FieldDescriptionInterface;

class Sorter implements SorterInterface
{
    /**
     * @var FieldDescriptionInterface
     */
    protected $field;

    /**
     * @var string
     */
    protected $direction;

    /**
     * {@inheritdoc}
     */
    public function getField()
    {
        return $this->field;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->field->getName();
    }

    /**
     * {@inheritdoc}
     */
    public function getDirection()
    {
        return $this->direction;
    }

    /**
     * {@inheritdoc}
     */
    public function setDirection($direction)
    {
        if (!is_null($direction)) {
            if (in_array($direction, array(self::DIRECTION_ASC, self::DIRECTION_DESC), true)) {
                $this->direction = $direction;
            } elseif ($direction) {
                $this->direction = self::DIRECTION_DESC;
            } else {
                $this->direction = self::DIRECTION_ASC;
            }
        }
    }

    /**
     * {@inheritdoc}
     */
    public function initialize(FieldDescriptionInterface $field, $direction = null)
    {
        $this->field = $field;
        $this->setDirection($direction);
    }

    /**
     * @param ProxyQueryInterface $queryInterface
     * @param string $direction
     *
     * @return void
     */
    public function apply(ProxyQueryInterface $queryInterface, $direction = null)
    {
        $this->setDirection($direction);
        $queryInterface->addSortOrder(
            $this->field->getSortParentAssociationMapping(),
            $this->field->getSortFieldMapping(),
            $this->getDirection()
        );
    }
}
