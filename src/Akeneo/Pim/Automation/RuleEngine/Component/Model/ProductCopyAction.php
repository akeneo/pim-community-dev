<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2014 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Pim\Automation\RuleEngine\Component\Model;

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

    public function __construct(array $data)
    {
        $this->fromField = isset($data['from_field']) ? strtolower($data['from_field']) : null;
        $this->toField = isset($data['to_field']) ? strtolower($data['to_field']) : null;
        $this->options = [
            'from_locale' => isset($data['from_locale']) ? $data['from_locale'] : null,
            'to_locale'   => isset($data['to_locale']) ? $data['to_locale'] : null,
            'from_scope'  => isset($data['from_scope']) ? $data['from_scope'] : null,
            'to_scope'    => isset($data['to_scope']) ? $data['to_scope'] : null
        ];
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
    public function getOptions()
    {
        return $this->options;
    }

    /**
     * {@inheritdoc}
     */
    public function getImpactedFields()
    {
        return [$this->getToField()];
    }
}
