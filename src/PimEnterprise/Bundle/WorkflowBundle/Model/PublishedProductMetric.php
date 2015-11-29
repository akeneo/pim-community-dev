<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2014 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PimEnterprise\Bundle\WorkflowBundle\Model;

use JMS\Serializer\Annotation\ExclusionPolicy;
use Pim\Component\Catalog\Model\AbstractMetric;

/**
 * Published product metric
 *
 * @author Nicolas Dupont <nicolas@akeneo.com>
 * @ExclusionPolicy("all")
 */
class PublishedProductMetric extends AbstractMetric implements PublishedProductMetricInterface
{
}
