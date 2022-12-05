<?php

declare(strict_types=1);

namespace Akeneo\Pim\Automation\IdentifierGenerator\Infrastructure\Validation;

use Symfony\Component\Validator\Constraint;

/**
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license   https://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class IdentifierGeneratorShouldExist extends Constraint
{
    public string $message = 'validation.update.identifier_generator_code_not_found';

    /**
     * @inerhitDoc
     */
    public function getTargets(): string
    {
        return self::CLASS_CONSTRAINT;
    }
}
