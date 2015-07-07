<?php

namespace Pim\Bundle\CatalogBundle\Entity\Repository;

use Pim\Bundle\CatalogBundle\Repository\ProductTemplateRepositoryInterface;
use Doctrine\ORM\EntityRepository;

/**
 * Product template Repository
 *
 * @author    Marie Bochu <marie.bochu@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 * @deprecated will be moved to Pim\Bundle\CatalogBundle\Doctrine\ORM\Repository in 1.4
 */
class ProductTemplateRepository extends EntityRepository implements ProductTemplateRepositoryInterface
{
}
