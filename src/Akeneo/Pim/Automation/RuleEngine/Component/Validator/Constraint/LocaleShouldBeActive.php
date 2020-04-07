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

namespace Akeneo\Pim\Automation\RuleEngine\Component\Validator\Constraint;

use Akeneo\Pim\Automation\RuleEngine\Component\Validator\LocaleShouldBeActiveValidator;
use Symfony\Component\Validator\Constraint;

/**
 * @author    Nicolas Marniesse <nicolas.marniesse@akeneo.com>
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 */
final class LocaleShouldBeActive extends Constraint
{
    public $message = 'The "%locale_code%" locale does not exist or is not activated';

    /**
     * {@inheritdoc}
     */
    public function validatedBy()
    {
        return LocaleShouldBeActiveValidator::class;
    }

    /**
     * {@inheritdoc}
     */
    public function getTargets()
    {
        return static::CLASS_CONSTRAINT;
    }
}
