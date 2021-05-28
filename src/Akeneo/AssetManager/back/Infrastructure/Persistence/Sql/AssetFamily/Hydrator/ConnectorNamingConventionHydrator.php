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

namespace Akeneo\AssetManager\Infrastructure\Persistence\Sql\AssetFamily\Hydrator;

use Akeneo\AssetManager\Domain\Model\AssetFamily\AssetFamilyIdentifier;
use Akeneo\AssetManager\Domain\Model\AssetFamily\NamingConvention\NamingConvention as DomainNamingConvention;
use Akeneo\AssetManager\Domain\Model\AssetFamily\NamingConvention\NamingConventionInterface;
use Akeneo\AssetManager\Domain\Model\AssetFamily\NamingConvention\NullNamingConvention;
use Akeneo\AssetManager\Infrastructure\Validation\AssetFamily\NamingConvention;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class ConnectorNamingConventionHydrator
{
    private ValidatorInterface $validator;

    public function __construct(ValidatorInterface $validator)
    {
        $this->validator = $validator;
    }

    public function hydrate(
        array $normalizedNamingConvention,
        AssetFamilyIdentifier $assetFamilyIdentifier
    ): NamingConventionInterface {
        $violations = $this->validator->validate(
            $normalizedNamingConvention,
            new NamingConvention($assetFamilyIdentifier)
        );
        if ($violations->count() === 0) {
            return DomainNamingConvention::createFromNormalized($normalizedNamingConvention);
        }

        return new NullNamingConvention();
    }
}
