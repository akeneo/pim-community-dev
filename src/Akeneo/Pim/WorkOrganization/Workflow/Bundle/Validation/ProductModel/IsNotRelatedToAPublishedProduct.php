<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2021 Akeneo SAS (https://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Pim\WorkOrganization\Workflow\Bundle\Validation\ProductModel;

use Symfony\Component\Validator\Constraint;

final class IsNotRelatedToAPublishedProduct extends Constraint
{
    public string $message = 'pimee_workflow.check_removal.product_model_error';

    public function getTargets(): array
    {
        return [self::CLASS_CONSTRAINT];
    }
}
