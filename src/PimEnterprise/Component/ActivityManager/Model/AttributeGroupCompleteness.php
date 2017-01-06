<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2017 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PimEnterprise\Component\ActivityManager\Model;

/**
 * Represent the attribute group completeness used in the pre processed data for the project completeness
 *
 * @author Arnaud Langlade <arnaud.langlade@akeneo.com>
 */
class AttributeGroupCompleteness
{
    /** @var int */
    protected $attributeGroupId;

    /** @var int */
    protected $hasAtLeastOneAttributeFilled;

    /** @var int */
    protected $isComplete;

    public function __construct($attributeGroupId, $hasAtLeastOneAttributeFilled, $isComplete)
    {
        $this->attributeGroupId = $attributeGroupId;
        $this->hasAtLeastOneAttributeFilled = $hasAtLeastOneAttributeFilled;
        $this->isComplete = $isComplete;
    }

    /**
     * Return the attribute group id
     *
     * @return int
     */
    public function getAttributeGroupId()
    {
        return $this->attributeGroupId;
    }

    /**
     * Return 1 if the attribute has at least one attribute filed otherwise 0
     *
     * @return int
     */
    public function hasAtLeastOneAttributeFilled()
    {
        return $this->hasAtLeastOneAttributeFilled;
    }

    /**
     * Return 1 if the attribute is complete otherwise 0
     *
     * @return int
     */
    public function isComplete()
    {
        return $this->isComplete;
    }
}
