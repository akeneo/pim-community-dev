<?php

namespace Oro\Bundle\PimDataGridBundle\Entity;

use Akeneo\UserManagement\Component\Model\UserInterface;

/**
 * Datagrid view entity
 *
 * @author    Filips Alpe <filips@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class DatagridView
{
    /** @staticvar string */
    const TYPE_PUBLIC = 'public';

    /** @var int */
    protected $id;

    /** @var string */
    protected $label;

    /** @var string */
    protected $type = self::TYPE_PUBLIC;

    /** @var UserInterface */
    protected $owner;

    /** @var string */
    protected $datagridAlias;

    /** @var array */
    protected $columns = [];

    /** @var string */
    protected $filters;

    /**
     * Indicates whether a view can be seen by users who don't own it
     *
     * @return bool
     */
    public function isPublic()
    {
        return $this->type === self::TYPE_PUBLIC;
    }

    /**
     * Get id
     *
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set label
     *
     * @param string $label
     *
     * @return DatagridView
     */
    public function setLabel($label)
    {
        $this->label = $label;

        return $this;
    }

    /**
     * Get label
     *
     * @return string
     */
    public function getLabel()
    {
        return $this->label;
    }

    /**
     * Set type
     *
     * @param string $type
     *
     * @return DatagridView
     */
    public function setType($type)
    {
        $this->type = $type;

        return $this;
    }

    /**
     * Get type
     *
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * Set owner
     *
     * @param UserInterface $owner
     *
     * @return DatagridView
     */
    public function setOwner(UserInterface $owner)
    {
        $this->owner = $owner;

        return $this;
    }

    /**
     * Get owner
     *
     * @return UserInterface
     */
    public function getOwner()
    {
        return $this->owner;
    }

    /**
     * Set datagrid alias
     *
     * @param string $datagridAlias
     *
     * @return DatagridView
     */
    public function setDatagridAlias($datagridAlias)
    {
        $this->datagridAlias = $datagridAlias;

        return $this;
    }

    /**
     * Get datagrid alias
     *
     * @return string
     */
    public function getDatagridAlias()
    {
        return $this->datagridAlias;
    }

    /**
     * Set columns
     *
     * @param array $columns
     *
     * @return DatagridView
     */
    public function setColumns(array $columns)
    {
        $this->columns = $columns;

        return $this;
    }

    /**
     * Get columns
     *
     * @return array
     */
    public function getColumns()
    {
        return $this->columns;
    }

    /**
     * Set column order
     *
     * @param string $order
     *
     * @return DatagridView
     */
    public function setOrder($order)
    {
        $this->columns = empty($order) ? [] : explode(',', $order);

        return $this;
    }

    /**
     * Get column order
     *
     * @return string
     */
    public function getOrder()
    {
        return implode(',', $this->columns);
    }

    /**
     * Set filters
     *
     * @param string $filters
     *
     * @return DatagridView
     */
    public function setFilters($filters)
    {
        $this->filters = $filters;

        return $this;
    }

    /**
     * Get filters
     *
     * @return string
     */
    public function getFilters()
    {
        return $this->filters;
    }
}
