<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2019 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\AssetManager\Domain\Model\AssetFamily\Transformation\Operation;

trait OperationExtraParameterTrait
{
    private static function assertNoExtraParameters(array $parameters, array $allowedParameters): void
    {
        $extraFields = array_keys(array_diff_key($parameters, array_flip($allowedParameters)));
        if (count($extraFields) > 0) {
            $messages = [];
            foreach ($extraFields as $extraField) {
                $messages[] = sprintf(
                    'The property "%s" was not expected.',
                    $extraField
                );
            }

            throw new \InvalidArgumentException(implode(' ', $messages));
        }
    }
}
