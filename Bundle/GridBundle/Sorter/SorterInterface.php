<?php

namespace Oro\Bundle\GridBundle\Sorter;

use Oro\Bundle\GridBundle\Datagrid\ProxyQueryInterface;
use Oro\Bundle\GridBundle\Field\FieldDescriptionInterface;

interface SorterInterface
{
    /**
     * Ascending sorting direction
     */
    const DIRECTION_ASC = "ASC";

    /**
     * Descending sorting direction
     */
    const DIRECTION_DESC = "DESC";

    /**
     * @return string
     */
    public function getName();

    /**
     * @return string
     */
    public function getField();

    /**
     * @return string
     */
    public function getDirection();

    /**
     * @param mixed $direction
     * @return void
     */
    public function setDirection($direction);

    /**
     * @param FieldDescriptionInterface $field
     * @param string $direction
     *
     * @return void
     */
    public function initialize(FieldDescriptionInterface $field, $direction = null);

    /**
     * @param ProxyQueryInterface $queryInterface
     * @param null $direction
     *
     * @return void
     */
    public function apply(ProxyQueryInterface $queryInterface, $direction = null);
}
