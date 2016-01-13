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
 * Copy action used in product rules.
 * A copy action value is used to copy a product source value to a product target value.
 *
 * For example : description-fr_FR-ecommerce to description-fr_CH-tablet
 * @deprecated will be removed in 1.6 please use
 *             PimEnterprise\Component\CatalogRule\Model\ProductCopyAction
 *
 * @author Julien Janvier <jjanvier@akeneo.com>
 */
class ProductCopyValueAction implements ProductCopyValueActionInterface
{
    /** @var string */
    protected $fromField;

    /** @var mixed */
    protected $fromLocale;

    /** @var string */
    protected $fromScope;

    /** @var string */
    protected $toField;

    /** @var mixed */
    protected $toLocale;

    /** @var string */
    protected $toScope;

    /**
     * @param array $data
     */
    public function __construct(array $data)
    {
        $this->fromField  = isset($data['from_field']) ? $data['from_field'] : null;
        $this->fromLocale = isset($data['from_locale']) ? $data['from_locale'] : null;
        $this->fromScope  = isset($data['from_scope']) ? $data['from_scope'] : null;
        $this->toField    = isset($data['to_field']) ? $data['to_field'] : null;
        $this->toLocale   = isset($data['to_locale']) ? $data['to_locale'] : null;
        $this->toScope    = isset($data['to_scope']) ? $data['to_scope'] : null;
    }

    /**
     * {@inheritdoc}
     */
    public function getFromField()
    {
        return $this->fromField;
    }

    /**
     * {@inheritdoc}
     */
    public function getFromLocale()
    {
        return $this->fromLocale;
    }

    /**
     * {@inheritdoc}
     */
    public function getFromScope()
    {
        return $this->fromScope;
    }

    /**
     * {@inheritdoc}
     */
    public function getToField()
    {
        return $this->toField;
    }

    /**
     * {@inheritdoc}
     */
    public function getToLocale()
    {
        return $this->toLocale;
    }

    /**
     * {@inheritdoc}
     */
    public function getToScope()
    {
        return $this->toScope;
    }

    /**
     * {@inheritdoc}
     */
    public function setFromField($fromField)
    {
        $this->fromField = $fromField;
    }

    /**
     * {@inheritdoc}
     */
    public function setFromLocale($fromLocale)
    {
        $this->fromLocale = $fromLocale;
    }

    /**
     * {@inheritdoc}
     */
    public function setFromScope($fromScope)
    {
        $this->fromScope = $fromScope;
    }

    /**
     * {@inheritdoc}
     */
    public function setToField($toField)
    {
        $this->toField = $toField;
    }

    /**
     * {@inheritdoc}
     */
    public function setToLocale($toLocale)
    {
        $this->toLocale = $toLocale;
    }

    /**
     * {@inheritdoc}
     */
    public function setToScope($toScope)
    {
        $this->toScope = $toScope;
    }

    /**
     * {@inheritdoc}
     */
    public function getImpactedFields()
    {
        return [$this->getToField()];
    }
}
