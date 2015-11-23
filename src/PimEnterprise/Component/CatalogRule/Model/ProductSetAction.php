<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2014 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PimEnterprise\Component\CatalogRule\Model;

use PimEnterprise\Component\CatalogRule\Model\ProductSetActionInterface;

/**
 * Set action is used in product rules.
 * A set action is used to set data to a product value
 *
 * For example : set ['socks', 'sexy_socks'] to categories
 * For example : set 'Nice name' to name
 *
 * @author Julien Sanchez <julien@akeneo.com>
 */
class ProductSetAction implements ProductSetActionInterface
{
    /** @var string */
    protected $field;

    /** @var mixed */
    protected $data;

    /** @var array */
    protected $options = [];

    /**
     * @param array $data
     */
    public function __construct(array $data)
    {
        $this->field   = isset($data['field']) ? $data['field'] : null;
        $this->data    = isset($data['data']) ? $data['data'] : [];
        $this->options = isset($data['options']) ? $data['options'] : [];
    }

    /**
     * {@inheritdoc}
     */
    public function getField()
    {
        return $this->field;
    }

    /**
     * {@inheritdoc}
     */
    public function setField($field)
    {
        $this->field = $field;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getData()
    {
        return $this->data;
    }

    /**
     * {@inheritdoc}
     */
    public function setData($data)
    {
        $this->data = $data;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getOptions()
    {
        return $this->options;
    }

    /**
     * {@inheritdoc}
     */
    public function setOptions(array $options = [])
    {
        $this->options = $options;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getImpactedFields()
    {
        return [$this->getField()];
    }
}
