<?php

namespace Akeneo\Pim\Enrichment\Bundle\MassEditAction\Operation;

/**
 * A "batchable" operation to make it works through the BatchBundle
 *
 * @author    Adrien Pétremann <adrien.petremann@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
interface BatchableOperationInterface
{
    /**
     * Get configuration to send to the BatchBundle command
     *
     * @return array
     */
    public function getBatchConfig();

    /**
     * Get the code of the JobInstance
     *
     * @return string
     */
    public function getJobInstanceCode();
}
