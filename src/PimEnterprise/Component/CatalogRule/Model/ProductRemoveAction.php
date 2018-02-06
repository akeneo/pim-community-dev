<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2016 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PimEnterprise\Component\CatalogRule\Model;

/**
 * {@inheritdoc}
 */
class ProductRemoveAction implements ProductRemoveActionInterface
{
    /** @var string */
    protected $field;

    /** @var array */
    protected $items;

    /** @var array */
    protected $options;

    /**
     * @param array $data
     */
    public function __construct(array $data)
    {
        $this->field = isset($data['field']) ? $data['field'] : null;
        $this->items = isset($data['items']) ? $data['items'] : [];
        $this->options = [
            'locale' => isset($data['locale']) ? $data['locale'] : null,
            'scope'  => isset($data['scope']) ? $data['scope'] : null
        ];

        if (isset($data['include_children'])) {
            $this->options['include_children'] = $data['include_children'];
        }
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
    public function getItems()
    {
        return $this->items;
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
        return [$this->getField()];
    }
}
