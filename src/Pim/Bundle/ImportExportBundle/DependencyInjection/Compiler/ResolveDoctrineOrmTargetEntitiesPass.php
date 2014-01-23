<?php

namespace Pim\Bundle\ImportExportBundle\DependencyInjection\Compiler;

use Pim\Bundle\CatalogBundle\DependencyInjection\Compiler\AbstractResolveDoctrineOrmTargetEntitiesPass;

/**
 * Resolves doctrine ORM Target entities
 *
 * @author    Antoine Guigan <antoine@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ResolveDoctrineOrmTargetEntitiesPass extends AbstractResolveDoctrineOrmTargetEntitiesPass
{
    /**
     * {@inheritdoc}
     */
    protected function getParametersMapping()
    {
        return [
            'Pim\Bundle\ImportExportBundle\Model\ExportInterface' => 'pim_import_export.entity.export.class',
        ];
    }
}
