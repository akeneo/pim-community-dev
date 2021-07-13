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

namespace Akeneo\Platform\TailoredExport\Infrastructure\Validation\Source\SimpleAssociationType;

use Akeneo\Platform\TailoredExport\Application\Query\Selection\SimpleAssociations\SimpleAssociationsSelectionInterface;
use Akeneo\Platform\TailoredExport\Infrastructure\Validation\ChannelShouldExist;
use Akeneo\Platform\TailoredExport\Infrastructure\Validation\LocaleShouldBeActive;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\Constraints\Choice;
use Symfony\Component\Validator\Constraints\Collection;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Optional;
use Symfony\Component\Validator\Constraints\Type;
use Symfony\Component\Validator\ConstraintValidator;

class SimpleAssociationSelectionValidator extends ConstraintValidator
{
    private const ENTITY_TYPES_WITH_CHANNEL = [
        SimpleAssociationsSelectionInterface::ENTITY_TYPE_PRODUCTS,
        SimpleAssociationsSelectionInterface::ENTITY_TYPE_PRODUCT_MODELS
    ];

    /** @var string[] */
    private array $availableCollectionSeparator;

    /** @var string[] $availableCollectionSeparator */
    public function __construct(array $availableCollectionSeparator)
    {
        $this->availableCollectionSeparator = $availableCollectionSeparator;
    }

    public function validate($selection, Constraint $constraint): void
    {
        $this->context->getValidator()
            ->inContext($this->context)
            ->validate($selection, [
                new Collection(
                    [
                        'fields' => [
                            'entity_type' => new Choice(
                                [
                                    'choices' => [
                                        SimpleAssociationsSelectionInterface::ENTITY_TYPE_PRODUCTS,
                                        SimpleAssociationsSelectionInterface::ENTITY_TYPE_PRODUCT_MODELS,
                                        SimpleAssociationsSelectionInterface::ENTITY_TYPE_GROUPS,
                                    ],
                                ]
                            ),
                            'type' => new Choice(
                                [
                                    'choices' => [
                                        'code',
                                        'label',
                                    ],
                                ]
                            ),
                            'separator' => new Choice(
                                [
                                    'choices' => $this->availableCollectionSeparator,
                                ]
                            ),
                            'locale' => new Optional([new Type(['type' => 'string'])]),
                            'channel' => new Optional([new Type(['type' => 'string'])]),
                        ],
                    ]
                ),
            ]);

        if ('label' === $selection['type']) {
            if (in_array($selection['entity_type'], self::ENTITY_TYPES_WITH_CHANNEL)) {
                $this->context->getValidator()
                    ->inContext($this->context)
                    ->atPath('[channel]')
                    ->validate($selection['channel'] ?? null, [
                        new NotBlank(),
                        new ChannelShouldExist()
                    ]);
            }

            $this->context->getValidator()
                ->inContext($this->context)
                ->atPath('[locale]')
                ->validate($selection['locale'] ?? null, [
                    new NotBlank(),
                    new LocaleShouldBeActive()
                ]);
        }
    }
}
