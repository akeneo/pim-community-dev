<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2020 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Pim\Enrichment\ReferenceEntity\Component\Validator\Constraints;

use Symfony\Component\Validator\Constraint;

final class RecordsShouldExist extends Constraint
{
    public $message = 'pim_reference_entity.product_value.validation.record_should_exist';
    public $messagePlural = 'pim_reference_entity.product_value.validation.records_should_exist';

    public function validatedBy(): string
    {
        return 'akeneo_pim_enrichment_records_should_exist';
    }
}
