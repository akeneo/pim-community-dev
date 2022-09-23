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

namespace Akeneo\Platform\Syndication\Infrastructure\Validation\Source\QuantifiedAssociationType;

use Akeneo\Platform\Syndication\Application\Common\Selection\QuantifiedAssociations\QuantifiedAssociationsCodeSelection;
use Akeneo\Platform\Syndication\Application\Common\Selection\QuantifiedAssociations\QuantifiedAssociationsLabelSelection;
use Akeneo\Platform\Syndication\Application\Common\Selection\QuantifiedAssociations\QuantifiedAssociationsQuantitySelection;
use Akeneo\Platform\Syndication\Application\Common\Selection\QuantifiedAssociations\QuantifiedAssociationsSelectionInterface;
use Akeneo\Platform\Syndication\Infrastructure\Validation\ChannelShouldExist;
use Akeneo\Platform\Syndication\Infrastructure\Validation\LocaleShouldBeActive;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\Constraints\Choice;
use Symfony\Component\Validator\Constraints\Collection;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Optional;
use Symfony\Component\Validator\Constraints\Type;
use Symfony\Component\Validator\ConstraintValidator;

class QuantifiedAssociationSelectionValidator extends ConstraintValidator
{
    /** @var string[] */
    private array $availableCollectionSeparator;

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
                                        QuantifiedAssociationsSelectionInterface::ENTITY_TYPE_PRODUCTS,
                                        QuantifiedAssociationsSelectionInterface::ENTITY_TYPE_PRODUCT_MODELS,
                                    ],
                                ]
                            ),
                            'type' => new Choice(
                                [
                                    'choices' => [
                                        QuantifiedAssociationsCodeSelection::TYPE,
                                        QuantifiedAssociationsLabelSelection::TYPE,
                                        QuantifiedAssociationsQuantitySelection::TYPE,
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
            $this->context->getValidator()
                ->inContext($this->context)
                ->atPath('[channel]')
                ->validate($selection['channel'] ?? null, [
                    new NotBlank(),
                    new ChannelShouldExist()
                ]);

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
