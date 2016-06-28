<?php

namespace Pim\Bundle\ImportExportBundle\JobTemplate;

use Akeneo\Component\Batch\Model\JobInstance;

/**
 * Registers job templates and generates template codes based on given job instance
 *
 * @author    Samir Boulil <samir.boulil@akeneo.com>
 * @copyright 2016 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class JobTemplateProvider implements JobTemplateProviderInterface
{
    /** @staticvar string */
    const DEFAULT_CREATE_TEMPLATE = 'PimImportExportBundle:%sProfile:create.html.twig';

    /** @staticvar string */
    const DEFAULT_SHOW_TEMPLATE   = 'PimImportExportBundle:%sProfile:show.html.twig';

    /** @staticvar string */
    const DEFAULT_EDIT_TEMPLATE   = 'PimImportExportBundle:%sProfile:edit.html.twig';

    /** @var array */
    protected $jobTemplates = [];

    /**
     * As parsed in the configuration file
     *
     * @param array $jobTemplates
     */
    public function __construct(array $jobTemplates)
    {
        $this->jobTemplates = $jobTemplates;
    }

    /**
     * {@inheritdoc}
     */
    public function getCreateTemplate(JobInstance $jobInstance)
    {
        return sprintf(self::DEFAULT_CREATE_TEMPLATE, ucfirst($jobInstance->getType()));
    }

    /**
     * {@inheritdoc}
     */
    public function getShowTemplate(JobInstance $jobInstance)
    {
        $jobName = $jobInstance->getJobName();

        if (isset($this->jobTemplates[$jobName]['templates']['show'])) {
            return $this->jobTemplates[$jobName]['templates']['show'];
        }

        return sprintf(self::DEFAULT_SHOW_TEMPLATE, ucfirst($jobInstance->getType()));
    }

    /**
     * {@inheritdoc}
     */
    public function getEditTemplate(JobInstance $jobInstance)
    {
        $jobName = $jobInstance->getJobName();

        if (isset($this->jobTemplates[$jobName]) &&
            isset($this->jobTemplates[$jobName]['templates']['edit'])) {
            return $this->jobTemplates[$jobName]['templates']['edit'];
        }

        return sprintf(self::DEFAULT_EDIT_TEMPLATE, ucfirst($jobInstance->getType()));
    }
}
