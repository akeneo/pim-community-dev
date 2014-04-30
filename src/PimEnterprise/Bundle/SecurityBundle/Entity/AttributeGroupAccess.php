<?php

namespace PimEnterprise\Bundle\SecurityBundle\Entity;

/**
 * Attribute Group Access entity
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 */
class AttributeGroupAccess
{
    /**
     * @var integer $id
     */
    protected $id;

    /**
     * @var integer $attributeGroupId
     */
    protected $attributeGroupId;

    /**
     * @var integer $roleId
     */
    protected $roleId;

    /**
     * @var boolean $viewAttributes
     */
    protected $viewAttributes;

    /**
     * @var boolean $editAttributes
     */
    protected $editAttributes;
}
