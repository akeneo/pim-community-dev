<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2014 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PimEnterprise\Bundle\WorkflowBundle\Presenter;

use Pim\Component\Catalog\AttributeTypes;

/**
 * Present text data
 *
 * @author Julien Sanchez <julien@akeneo.com>
 */
class TextPresenter extends AbstractProductValuePresenter
{
    /**
     * {@inheritdoc}
     */
    public function supportsChange($attributeType)
    {
        return AttributeTypes::TEXT === $attributeType;
    }

    /**
     * {@inheritdoc}
     */
    protected function normalizeData($data)
    {
        return $data;
    }

    /**
     * {@inheritdoc}
     */
    protected function normalizeChange(array $change)
    {
        return $change['data'];
    }
}
