<?php

namespace PimEnterprise\Bundle\WorkflowBundle\Doctrine\MongoDBODM;

use Pim\Bundle\CatalogBundle\Doctrine\MongoDBODM\ProductRepository;
use PimEnterprise\Bundle\WorkflowBundle\Repository\PublishedProductRepositoryInterface;

/**
 * Published products repository
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 */
class PublishedProductRepository extends ProductRepository implements PublishedProductRepositoryInterface
{
}
