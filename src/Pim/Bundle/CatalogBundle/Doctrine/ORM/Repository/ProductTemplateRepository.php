<?php

namespace Pim\Bundle\CatalogBundle\Doctrine\ORM;

use Doctrine\ORM\EntityRepository;
use Pim\Bundle\CatalogBundle\Repository\ProductTemplateRepositoryInterface;

/**
 * Product template repository
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ProductTemplateRepository extends EntityRepository implements ProductTemplateRepositoryInterface
{
    /**
     * @return ProductTemplateInterface[]
     */
    public function getTemplates()
    {
        return $this->findBy([]);
    }
} 