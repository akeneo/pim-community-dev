<?php

declare(strict_types=1);

namespace Akeneo\Tool\Bundle\BatchBundle\EventListener;

use Doctrine\ORM\Tools\Event\GenerateSchemaEventArgs;

/**
 * @copyright 2023 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class AddIsVisibleColumnToSchemaListener
{
    public function postGenerateSchema(GenerateSchemaEventArgs $eventArgs): void
    {
        $table = $eventArgs->getSchema()->getTable('akeneo_batch_job_instance');
        if (!$table->hasColumn('is_visible')) {
            $table->addColumn('is_visible', 'boolean', ['default' => true]);
        }
    }
}
