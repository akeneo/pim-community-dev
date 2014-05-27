<?php

namespace PimEnterprise\Bundle\WorkflowBundle\Model;

use JMS\Serializer\Annotation\ExclusionPolicy;
use Pim\Bundle\CatalogBundle\Model\AbstractProductValue;

/**
 * Published product value, business code is in AbstractProductValue
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 *
 * @ExclusionPolicy("all")
 */
class PublishedProductValue extends AbstractProductValue
{
}
