<?php
declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Domain\Settings\Validation\Connection;

use Akeneo\Connectivity\Connection\Domain\Settings\Model\ValueObject\ConnectionImage;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

/**
 * @author    Willy Mesnage <willy.mesnage@akeneo.com>
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class ConnectionImageMustBeValid
{
    public static function validate(?string $image, ExecutionContextInterface $context): void
    {
        if (null === $image) {
            return;
        }

        try {
            new ConnectionImage($image);
        } catch (\InvalidArgumentException $e) {
            $context->buildViolation($e->getMessage())->addViolation();
        }
    }
}
