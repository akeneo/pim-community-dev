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

namespace Akeneo\Platform\TailoredExport\Infrastructure\Validation\Source;

use Akeneo\Platform\TailoredExport\Domain\Model\Source\AssociationTypeSource;
use Akeneo\Platform\TailoredExport\Domain\Model\Source\AttributeSource;
use Akeneo\Platform\TailoredExport\Domain\Model\Source\PropertySource;
use Symfony\Component\Validator\Constraints\Choice;
use Symfony\Component\Validator\Constraints\Collection;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Type;
use Symfony\Component\Validator\Constraints\Uuid;

class SourceConstraintProvider
{
    public static function getConstraintCollection(): Collection
    {
        return new Collection([
            'fields' => [
                'uuid' => [
                    new NotBlank(),
                    new Uuid()
                ],
                'code' => [
                    new Type([
                        'type' => 'string',
                    ]),
                    new NotBlank(),
                ],
                'channel' => [
                    new Type([
                        'type' => 'string',
                    ]),
                ],
                'locale' => [
                    new Type([
                        'type' => 'string',
                    ]),
                ],
                'type' =>  new Choice([
                    'choices' => [AttributeSource::TYPE, PropertySource::TYPE, AssociationTypeSource::TYPE],
                ]),
            ],
        ]);
    }
}
