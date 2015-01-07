<?php

namespace Pim\Bundle\CatalogBundle\Repository;

use Pim\Bundle\CatalogBundle\Model\ProductTemplateInterface;

/**
 * Product template repository interface
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
interface ProductTemplateRepositoryInterface
{
    /**
     * @return ProductTemplateInterface[]
     */
    public function getTemplates();
}
