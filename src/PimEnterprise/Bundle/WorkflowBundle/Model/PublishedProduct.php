<?php

namespace PimEnterprise\Bundle\WorkflowBundle\Model;

use JMS\Serializer\Annotation\ExclusionPolicy;
use Pim\Bundle\CatalogBundle\Model\AbstractProduct;
use Pim\Bundle\CatalogBundle\Model\ReferableInterface;

/**
 * Published product, business code is in AbstractProduct
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 *
 * @ExclusionPolicy("all")
 */
class PublishedProduct extends AbstractProduct implements ReferableInterface
{
}
