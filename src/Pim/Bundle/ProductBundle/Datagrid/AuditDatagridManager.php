<?php

namespace Pim\Bundle\ProductBundle\Datagrid;

use Oro\Bundle\DataAuditBundle\Datagrid\AuditDatagridManager as BaseAuditDatagridManager;

/**
 * @author    Gildas Quemener <gildas.quemener@gmail.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class AuditDatagridManager extends BaseAuditDatagridManager
{
    protected function createQuery()
    {
        $query = parent::createQuery();

        $query->andWhere(
            $query->expr()->eq(
                'a.objectClass',
                $query->expr()->literal('Pim\\Bundle\\ProductBundle\\Entity\\Product')
            )
        );

        $query->andWhere(
            $query->expr()->eq(
                'a.objectId',
                $query->expr()->literal(1)
            )
        );
        die(var_dump($this->parameters->get('product_id')));

        return $query;
    }
}

