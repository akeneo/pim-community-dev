<?php

namespace Pim\Bundle\CatalogBundle\Entity;

use Oro\Bundle\UserBundle\Entity\User;

/**
 *
 * @author    Gildas Quemener <gildas@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class DatagridConfiguration
{
    /** @var integer */
    protected $id;

    /** @var string */
    protected $datagridAlias;

    /** @var array */
    protected $columns = [];

    /** @var User */
    protected $user;

    public function getId()
    {
        return $this->id;
    }

    /**
     * Set the datagrid alias
     *
     * @param string $datagridAlias
     */
    public function setDatagridAlias($datagridAlias)
    {
        $this->datagridAlias = $datagridAlias;
    }

    /**
     * Get the datagrid alias
     *
     * @return string
     */
    public function getDatagridAlias()
    {
        return $this->datagridAlias;
    }

    /**
     * Set the columns
     *
     * @param array $columns
     */
    public function setColumns($columns)
    {
        $this->columns = $columns;
    }

    /**
     * Get the columns
     *
     * @return array
     */
    public function getColumns()
    {
        return $this->columns;
    }

    /**
     * Set columns order
     *
     * @param string $order
     */
    public function setOrder($order)
    {
        $this->columns = explode(',', $order);
    }

    /**
     * Get columns order
     *
     * @return string
     */
    public function getOrder()
    {
        return join(',', $this->columns);
    }

    public function setUser(User $user)
    {
        $this->user = $user;
    }

    public function getUser()
    {
        return $this->user;
    }
}
