<?php

namespace Pim\Bundle\CatalogBundle\Doctrine\ORM\Repository;

use Doctrine\ORM\EntityRepository;
use Pim\Bundle\CatalogBundle\Repository\AttributeRequirementRepositoryInterface;

/**
 * Repository for attribute requirement entity
 *
 * @author    Olivier Soulet <olivier.soulet@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class AttributeRequirementRepository extends EntityRepository implements AttributeRequirementRepositoryInterface
{
}
