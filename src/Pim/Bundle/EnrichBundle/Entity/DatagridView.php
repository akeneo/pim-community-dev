<?php

namespace Pim\Bundle\EnrichBundle\Entity;

use Oro\Bundle\UserBundle\Entity\User;

/**
 * Datagrid view entity
 *
 * @author    Filips Alpe <filips@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class DatagridView
{
    /** @var integer */
    protected $id;

    /** @var string */
    protected $label;

    /** @var User */
    protected $owner;

    /** @var string */
    protected $datagridAlias;

    /** @var array */
    protected $columns = [];

    /** @var string */
    protected $filters;

    /**
     * Get id
     *
     * @return integer
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
     * Set owner
     *
     * @param User $owner
     *
     * @return DatagridView
     */
    public function setOwner(User $owner)
    {
        $this->owner = $owner;

        return $this;
    }

    /**
     * Get owner
     *
     * @return User
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
