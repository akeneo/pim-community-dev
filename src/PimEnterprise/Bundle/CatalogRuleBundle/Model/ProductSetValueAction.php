<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2014 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PimEnterprise\Bundle\CatalogRuleBundle\Model;

/**
 * Set action used in product rules.
 * A set action value is used to set a product source field (or product value) with a given value
 * for a scope and a locale.
 *
 * For example : set description-fr_FR-ecommerce to 'foo'
 *
 * @author Julien Janvier <jjanvier@akeneo.com>
 */
class ProductSetValueAction implements ProductSetValueActionInterface
{
    /** @var string */
    protected $field;

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
        $this->field  = isset($data['field']) ? $data['field'] : null;
        $this->value  = isset($data['value']) ? $data['value'] : null;
        $this->locale = isset($data['locale']) ? $data['locale'] : null;
        $this->scope  = isset($data['scope']) ? $data['scope'] : null;
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
    public function setValue($value)
    {
        $this->value = $value;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function setLocale($locale)
    {
        $this->locale = $locale;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function setScope($scope)
    {
        $this->scope = $scope;

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
