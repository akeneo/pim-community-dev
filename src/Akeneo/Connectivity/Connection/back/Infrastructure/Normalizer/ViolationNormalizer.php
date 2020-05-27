<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Infrastructure\Normalizer;

use Symfony\Component\Validator\ConstraintViolationInterface;

/**
 * Normalize a ViolationHttpException with all errors
 *
 * @author Pierre Jolly <pierre.jolly@akeneo.com>
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ViolationNormalizer extends \Akeneo\Tool\Component\Api\Normalizer\Exception\ViolationNormalizer
{
    protected function normalizeViolation(ConstraintViolationInterface $violation, array &$existingViolation): array
    {
        $error = parent::normalizeViolation($violation, $existingViolation);

        $error['raw_message'] = $violation->getMessageTemplate();
        $error['parameters'] = $violation->getParameters();
        $error['type'] = 'violation';

        return $error;
    }
}
