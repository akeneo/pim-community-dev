<?php

namespace PimEnterprise\Bundle\WorkflowBundle\Proposition;

use Pim\Bundle\CatalogBundle\Model\AbstractProductValue;

/**
 * Store product value changes and some metadata
 *
 * @author    Gildas Quemener <gildas@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 */
class ChangesCollector implements ChangesCollectorInterface
{
    /** @var array */
    protected $changes;

    /**
     * {@inheritdoc}
     */
    public function add($key, $changes, AbstractProductValue $value)
    {
        if (isset($this->changes['values'][$key])) {
            // Someone has already defined the changes applied to $key
            return;
        }

        // TODO (2014-07-03 10:15 by Gildas): Store data and metadata in 2 differents structures
        $this->changes['values'][$key] = array_merge(
            $changes,
            [
                '__context__' => [
                    'attribute_id' => $value->getAttribute()->getId(),
                    'value_id' => $value->getId(),
                    'scope' => $value->getScope(),
                    'locale' => $value->getLocale(),
                ]
            ]
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getChanges()
    {
        return $this->changes;
    }
}
