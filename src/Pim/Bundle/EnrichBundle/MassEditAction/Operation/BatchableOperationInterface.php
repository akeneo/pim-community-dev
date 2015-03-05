<?php

namespace Pim\Bundle\EnrichBundle\MassEditAction\Operation;

/**
 * @author    Adrien Pétremann <adrien.petremann@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
interface BatchableOperationInterface
{
    /**
     * Get the configuration to send to the BatchBundle command
     *
     * @return string
     */
    public function getBatchConfig();

    /**
     * Get the code of the JobInstance
     *
     * @return string
     */
    public function getBatchJobCode();
}
