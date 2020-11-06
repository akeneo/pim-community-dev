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
     */
    public function isPublic(): bool
    {
        return $this->type === self::TYPE_PUBLIC;
    }

    /**
     * Get id
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * Set label
     *
     * @param string $label
     */
    public function setLabel(string $label): self
    {
        $this->label = $label;

        return $this;
    }

    /**
     * Get label
     */
    public function getLabel(): string
    {
        return $this->label;
    }

    /**
     * Set type
     *
     * @param string $type
     */
    public function setType(string $type): self
    {
        $this->type = $type;

        return $this;
    }

    /**
     * Get type
     */
    public function getType(): string
    {
        return $this->type;
    }

    /**
     * Set owner
     *
     * @param UserInterface $owner
     */
    public function setOwner(UserInterface $owner): self
    {
        $this->owner = $owner;

        return $this;
    }

    /**
     * Get owner
     */
    public function getOwner(): \Akeneo\UserManagement\Component\Model\UserInterface
    {
        return $this->owner;
    }

    /**
     * Set datagrid alias
     *
     * @param string $datagridAlias
     */
    public function setDatagridAlias(string $datagridAlias): self
    {
        $this->datagridAlias = $datagridAlias;

        return $this;
    }

    /**
     * Get datagrid alias
     */
    public function getDatagridAlias(): string
    {
        return $this->datagridAlias;
    }

    /**
     * Set columns
     *
     * @param array $columns
     */
    public function setColumns(array $columns): self
    {
        $this->columns = $columns;

        return $this;
    }

    /**
     * Get columns
     */
    public function getColumns(): array
    {
        return $this->columns;
    }

    /**
     * Set column order
     *
     * @param string $order
     */
    public function setOrder(string $order): self
    {
        $this->columns = empty($order) ? [] : explode(',', $order);

        return $this;
    }

    /**
     * Get column order
     */
    public function getOrder(): string
    {
        return implode(',', $this->columns);
    }

    /**
     * Set filters
     *
     * @param string $filters
     */
    public function setFilters(string $filters): self
    {
        $this->filters = $filters;

        return $this;
    }

    /**
     * Get filters
     */
    public function getFilters(): string
    {
        return $this->filters;
    }
}
