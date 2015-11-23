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
 * Copy action used in product rules.
 * A copy action is used to copy a product source field to a product target field.
 *
 * For example : description-fr_FR-ecommerce to description-fr_CH-tablet
 * For example : enabled to to_export-ecommerce
 *
 * @author Julien Janvier <jjanvier@akeneo.com>
 */
class ProductCopyAction implements ProductCopyActionInterface
{
    /** @var string */
    protected $fromField;

    /** @var string */
    protected $toField;

    /** @var array */
    protected $options;

    /**
     * @param array $data
     */
    public function __construct(array $data)
    {
        $this->fromField = isset($data['from_field']) ? $data['from_field'] : null;
        $this->toField   = isset($data['to_field']) ? $data['to_field'] : null;
        $this->options   = isset($data['options']) ? $data['options'] : [];
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
    public function getToField()
    {
        return $this->toField;
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
    public function getOptions()
    {
        return $this->options;
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
    public function setOptions($options)
    {
        $this->options = $options;
    }

    /**
     * {@inheritdoc}
     */
    public function getImpactedFields()
    {
        return [$this->getToField()];
    }
}
