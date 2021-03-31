<?php

declare(strict_types=1);

namespace Oro\Bundle\PimDataGridBundle\Validator\Constraints;

use Symfony\Component\Validator\Constraint;

/**
 * @author Pierre Jolly <pierre.jolly@akeneo.com>
 * @copyright 2021 Akeneo SAS (http://www.akeneo.com)
 * @license http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class UniqueDatagridViewEntity extends Constraint
{
    public $message = 'The same label is already set on another view';

    /**
     * {@inheritdoc}
     */
    public function validatedBy(): string
    {
        return 'pim_unique_datagrid_view_validator_entity';
    }

    /**
     * {@inheritdoc}
     */
    public function getTargets(): string
    {
        return self::CLASS_CONSTRAINT;
    }
}
