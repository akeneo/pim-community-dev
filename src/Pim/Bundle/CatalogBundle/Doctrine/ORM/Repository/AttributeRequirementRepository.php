<?php

namespace Pim\Bundle\CatalogBundle\Doctrine\ORM\Repository;

use Doctrine\ORM\EntityRepository;
use Pim\Component\Catalog\Repository\AttributeRequirementRepositoryInterface;

/**
 * Repository for attribute requirement entity
 *
 * @author    Remy Betus <remy.betus@akeneo.com>
 * @copyright 2016 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class AttributeRequirementRepository extends EntityRepository implements AttributeRequirementRepositoryInterface
{
}
