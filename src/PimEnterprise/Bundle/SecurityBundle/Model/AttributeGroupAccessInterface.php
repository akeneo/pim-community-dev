<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2014 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PimEnterprise\Bundle\SecurityBundle\Model;

use Pim\Component\Catalog\Model\AttributeGroupInterface;

/**
 * Attribute group access interface
 *
 * @author Julien Janvier <julien.janvier@akeneo.com>
 */
interface AttributeGroupAccessInterface extends AccessInterface
{
    /**
     * Get attribute group
     *
     * @return AttributeGroupInterface
     */
    public function getAttributeGroup();

    /**
     * Set attribute group
     *
     * @param AttributeGroupInterface $attributeGroup
     *
     * @return AttributeGroupAccessInterface
     */
    public function setAttributeGroup(AttributeGroupInterface $attributeGroup);

    /**
     * Predicate for view attributes permission
     *
     * @return bool
     */
    public function isViewAttributes();

    /**
     * Set view attributes permission
     *
     * @param bool $viewAttributes
     *
     * @return AttributeGroupAccessInterface
     */
    public function setViewAttributes($viewAttributes);

    /**
     * Predicate for edit attributes permission
     *
     * @return bool
     */
    public function isEditAttributes();

    /**
     * Set edit attributes permission
     *
     * @param bool $editAttributes
     *
     * @return AttributeGroupAccessInterface
     */
    public function setEditAttributes($editAttributes);
}
