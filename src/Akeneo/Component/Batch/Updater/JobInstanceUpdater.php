<?php

namespace Akeneo\Component\Batch\Updater;

use Akeneo\Component\Batch\Model\JobInstance;
use Akeneo\Component\StorageUtils\Updater\ObjectUpdaterInterface;
use Doctrine\Common\Util\ClassUtils;

/**
 * Update a job instance
 *
 * @author    Arnaud Langlade <arnaud.langlade@akeneo.com>
 * @copyright 2016 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class JobInstanceUpdater implements ObjectUpdaterInterface
{
    /**
     * {@inheritdoc}
     *
     * @param JobInstance $jobInstance
     */
    public function update($jobInstance, array $data, array $options = [])
    {
        if (!$jobInstance instanceof JobInstance) {
            throw new \InvalidArgumentException(
                sprintf(
                    'Expects a "Akeneo\Component\Batch\Model\JobInstance", "%s" provided.',
                    ClassUtils::getClass($jobInstance)
                )
            );
        }

        foreach ($data as $field => $value) {
            $this->setData($jobInstance, $field, $value);
        }
    }

    /**
     * @param JobInstance $jobInstance
     * @param string      $field
     * @param mixed       $data
     */
    protected function setData(JobInstance $jobInstance, $field, $data)
    {
        switch ($field) {
            case 'connector':
                $jobInstance->setConnector($data);
                break;
            case 'alias':
                $jobInstance->setAlias($data);
                break;
            case 'label':
                $jobInstance->setLabel($data);
                break;
            case 'type':
                $jobInstance->setType($data);
                break;
            case 'configuration':
                $jobInstance->setRawConfiguration($data);
                break;
            case 'code':
                $jobInstance->setCode($data);
                break;
        }
    }
}
