<?php

namespace Pim\Bundle\ImportExportBundle\Validator\Constraints;

use Akeneo\Component\Batch\Model\JobInstance;
use Symfony\Component\Validator\Constraint;

/**
 * Constraint for the field "Updated time condition" in export builder
 *
 * @author    Arnaud Langlade <arnaud.langlade@akeneo.com>
 * @copyright 2016 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class UpdatedSinceNDays extends Constraint
{
    /** @var JobInstance */
    public $jobInstance;

    /** @var string */
    public $message = 'pim_connector.export.updated.updated_since_n_days.error';

    /** @var string */
    public $strategy = 'since_n_days';
    
    /**
     * {@inheritdoc}
     */
    public function getDefaultOption()
    {
        return 'jobInstance';
    }

    /**
     * {@inheritdoc}
     */
    public function validatedBy()
    {
        return 'updated_since_strategy';
    }
}
