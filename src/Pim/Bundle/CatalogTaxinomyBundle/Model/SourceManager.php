<?php
namespace Pim\Bundle\CatalogTaxinomyBundle\Model;

use Oro\Bundle\FlexibleEntityBundle\Doctrine\BaseEntityManager;

/**
 * Manage sources
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2012 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class SourceManager extends BaseEntityManager
{

    /**
     * {@inheritdoc}
     */
    public function getEntityShortname()
    {
        return 'PimCatalogTaxinomyBundle:Source';
    }

}
