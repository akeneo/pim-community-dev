<?php

namespace Pim\Bundle\ImportExportBundle\Factory;

/**
 * Job instance factory
 *
 * @author    Romain Monceau <romain@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class JobInstanceFactory
{
    /** @var string */
    protected $jobInstanceClass;

    /**
     * @param string $jobInstanceClass
     */
    public function __construct($jobInstanceClass)
    {
        $this->jobInstanceClass = $jobInstanceClass;
    }

    /**
     * Create a job instance
     *
     * @param string $connector
     * @param string $type
     * @param string $alias
     *
     * @return JobInstance
     */
    public function createJobInstance($connector = null, $type = null, $alias = null)
    {
        return new $this->jobInstanceClass($connector, $type, $alias);
    }
}
