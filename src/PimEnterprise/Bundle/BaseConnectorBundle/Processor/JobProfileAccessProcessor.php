<?php

namespace PimEnterprise\Bundle\BaseConnectorBundle\Processor;

/**
 * Job profile access import processor
 * Allows to bind data into a job profile access and validate them
 *
 * @author    Romain Monceau <romain@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 */
class JobProfileAccessProcessor extends AbstractAccessProcessor
{
    /**
     * {@inheritdoc}
     */
    protected function getMapping()
    {
        return [
            'code' => 'jobProfile',
        ];
    }

    /**
     * {@inheritdoc}
     */
    protected function getSupportedPermissions()
    {
        return ['executeJobProfile', 'editJobProfile'];
    }
}
