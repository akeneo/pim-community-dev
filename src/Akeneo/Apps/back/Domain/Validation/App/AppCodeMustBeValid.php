<?php

declare(strict_types=1);

namespace Akeneo\Apps\Domain\Validation\App;

use Akeneo\Apps\Domain\Model\Write\AppCode;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

/**
 * @author Romain Monceau <romain@akeneo.com>
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 * @license http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
final class AppCodeMustBeValid
{
    public static function validate(string $appCode, ExecutionContextInterface $context): void
    {
        try {
            AppCode::create($appCode);
        } catch (\InvalidArgumentException $e) {
            $context->buildViolation($e->getMessage())->addViolation();
        }
    }
}
