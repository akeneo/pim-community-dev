<?php

namespace Akeneo\Platform\Bundle\ImportExportBundle\Provider\Form;

use Akeneo\Platform\Bundle\UIBundle\Provider\Form\FormProviderInterface;
use Akeneo\Tool\Component\Batch\Model\JobInstance;

/**
 * Form provider for job instance
 *
 * @author    Julien Sanchez <julien@akeneo.com>
 * @copyright 2016 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class JobInstanceFormProvider implements FormProviderInterface
{
    /** @var array */
    protected $formConfig;

    /**
     * @param array $formConfig
     */
    public function __construct($formConfig)
    {
        $this->formConfig = $formConfig;
    }

    /**
     * {@inheritdoc}
     */
    public function getForm($jobInstance): string
    {
        return $this->formConfig[$jobInstance->getJobName()];
    }

    /**
     * {@inheritdoc}
     */
    public function supports($element): bool
    {
        return $element instanceof JobInstance && isset($this->formConfig[$element->getJobName()]);
    }
}
