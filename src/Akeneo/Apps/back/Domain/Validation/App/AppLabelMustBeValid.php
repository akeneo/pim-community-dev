<?php

declare(strict_types=1);

namespace Akeneo\Apps\Domain\Validation\App;

use Akeneo\Apps\Domain\Model\Write\AppCode;
use Akeneo\Apps\Domain\Model\Write\AppLabel;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

/**
 * @author Romain Monceau <romain@akeneo.com>
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 * @license http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
final class AppLabelMustBeValid
{
    public static function validate(string $appLabel, ExecutionContextInterface $context): void
    {
        try {
            AppLabel::create($appLabel);
        } catch (\InvalidArgumentException $e) {
            $context->buildViolation($e->getMessage())->addViolation();
        }
    }
}
