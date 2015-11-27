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

/**
 * Condition used in product rules.
 * A product condition is used to select products given a product field (or a product value), for an operator
 * a value criteria.
 *
 * For example: SKU CONTAINS '%foo%'
 *
 * @author Julien Janvier <jjanvier@akeneo.com>
 */
class ProductCondition implements ProductConditionInterface
{
    /** @var string */
    protected $field;

    /** @var string */
    protected $operator;

    /** @var mixed */
    protected $value;

    /** @var string */
    protected $locale;

    /** @var string */
    protected $scope;

    /**
     * @param array $data
     */
    public function __construct(array $data)
    {
        $this->field    = isset($data['field']) ? $data['field'] : null;
        $this->operator = isset($data['operator']) ? $data['operator'] : null;
        $this->value    = isset($data['value']) ? $data['value'] : null;
        $this->locale   = isset($data['locale']) ? $data['locale'] : null;
        $this->scope    = isset($data['scope']) ? $data['scope'] : null;
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
    public function getOperator()
    {
        return $this->operator;
    }

    /**
     * {@inheritdoc}
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * {@inheritdoc}
     */
    public function getLocale()
    {
        return $this->locale;
    }

    /**
     * {@inheritdoc}
     */
    public function getScope()
    {
        return $this->scope;
    }
}
