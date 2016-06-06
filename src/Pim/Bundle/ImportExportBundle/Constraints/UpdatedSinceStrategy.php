<?php

namespace Pim\Bundle\ImportExportBundle\Constraints;

use Akeneo\Component\Batch\Model\JobInstance;
use Symfony\Component\Validator\Constraint;

/**
 * Constraint for the field "Updated time condition" in export builder
 *
 * @author    Arnaud Langlade <arnaud.langlade@akeneo.com>
 * @copyright 2016 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class UpdatedSinceStrategy extends Constraint
{
    /** @var string */
    public $strategy;
    
    /** @var JobInstance */
    public $jobInstance;
    
    /** @var string */
    public $message = [
        'since_date' => 'pim_connector.export.updated.updated_since_date.error',
        'since_period' => 'pim_connector.export.updated.updated_since_period.error',
    ];

    /**
     * {@inheritdoc}
     */
    public function getRequiredOptions()
    {
        return [
            'jobInstance',
            'strategy',
        ];
    }
}
