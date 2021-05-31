<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2018 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\AssetManager\Application\Attribute\CreateAttribute\CommandFactory;

/**
 * @author    Samir Boulil <samir.boulil@akeneo.com>
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 * @api
 */
abstract class AbstractCreateAttributeCommandFactory implements CreateAttributeCommandFactoryInterface
{
    /**
     * @throws \InvalidArgumentException
     */
    protected function checkCommonProperties(array $nomalizedCommand): void
    {
        $keysToCheck = [
            'code',
            'asset_family_identifier'
        ];

        foreach ($keysToCheck as $keyToCheck) {
            if (!array_key_exists($keyToCheck, $nomalizedCommand)) {
                throw new \InvalidArgumentException(
                    sprintf('Expects normalized command to have key "%s"', $keyToCheck)
                );
            }
        }
    }
}
