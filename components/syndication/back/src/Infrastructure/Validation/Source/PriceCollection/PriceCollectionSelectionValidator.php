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

namespace Akeneo\Platform\Syndication\Infrastructure\Validation\Source\PriceCollection;

use Akeneo\Channel\Infrastructure\Component\Query\PublicApi\FindActivatedCurrenciesInterface;
use Akeneo\Platform\Syndication\Application\Common\Selection\PriceCollection\PriceCollectionAmountSelection;
use Akeneo\Platform\Syndication\Application\Common\Selection\PriceCollection\PriceCollectionCurrencyCodeSelection;
use Akeneo\Platform\Syndication\Application\Common\Selection\PriceCollection\PriceCollectionCurrencyLabelSelection;
use Akeneo\Platform\Syndication\Application\Common\Selection\PriceCollection\PriceSelection;
use Akeneo\Platform\Syndication\Infrastructure\Validation\LocaleShouldBeActive;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\Constraints\Choice;
use Symfony\Component\Validator\Constraints\Collection;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Optional;
use Symfony\Component\Validator\Constraints\Type;
use Symfony\Component\Validator\ConstraintValidator;
use Webmozart\Assert\Assert;

class PriceCollectionSelectionValidator extends ConstraintValidator
{
    private array $availableCollectionSeparator;
    private FindActivatedCurrenciesInterface $findActivatedCurrencies;

    public function __construct(
        array $availableCollectionSeparator,
        FindActivatedCurrenciesInterface $findActivatedCurrencies
    ) {
        $this->availableCollectionSeparator = $availableCollectionSeparator;
        $this->findActivatedCurrencies = $findActivatedCurrencies;
    }

    public function validate($selection, Constraint $constraint): void
    {
        if (!$constraint instanceof PriceCollectionSelectionConstraint) {
            throw new \InvalidArgumentException('Invalid constraint');
        }

        $validator = $this->context->getValidator()->inContext($this->context);
        $validator->validate($selection, new Collection([ // We should have a dedicated constraint to manage price and price collection differently
            'fields' => [
                'type' => new Choice(
                    [
                        'choices' => [
                            PriceCollectionCurrencyCodeSelection::TYPE,
                            PriceCollectionCurrencyLabelSelection::TYPE,
                            PriceCollectionAmountSelection::TYPE,
                            PriceSelection::TYPE,
                        ],
                    ]
                ),
                'locale' => new Optional([new Type('string')]),
                'separator' => new Optional(new Choice(
                    [
                        'choices' => $this->availableCollectionSeparator,
                    ]
                )),
                'currencies' => new Optional(),
                'currency' => new Optional(),
            ],
        ]));

        if (PriceCollectionCurrencyLabelSelection::TYPE === $selection['type']) {
            $validator->atPath('[locale]')->validate($selection['locale'], [
                new NotBlank(),
                new LocaleShouldBeActive()
            ]);
        }

        if (array_key_exists('currencies', $selection)) {
            Assert::isArray($selection['currencies']);

            $this->validateCurrenciesAreActive($selection['currencies'], $constraint->channelReference);
        }
    }

    private function validateCurrenciesAreActive(array $currencyCodes, ?string $channelReference): void
    {
        $activatedCurrencies = $this->getActivatedCurrencyCodes($channelReference);
        $inactiveCurrencies = array_diff($currencyCodes, $activatedCurrencies);

        if (!empty($inactiveCurrencies)) {
            $errorMessage = $channelReference ?
                PriceCollectionSelectionConstraint::CURRENCY_SHOULD_BE_ACTIVATE_ON_CHANNEL_MESSAGE :
                PriceCollectionSelectionConstraint::CURRENCY_SHOULD_BE_ACTIVATE_MESSAGE;

            $this
                ->context
                ->buildViolation($errorMessage, [
                    '{{ channel_code }}' => $channelReference,
                    '{{ currency_codes }}' => implode(', ', $inactiveCurrencies)
                ])
                ->setPlural(count($inactiveCurrencies))
                ->atPath('[currencies]')
                ->addViolation();
        }
    }

    private function getActivatedCurrencyCodes(?string $channelReference): array
    {
        if (null === $channelReference) {
            return $this->findActivatedCurrencies->forAllChannels();
        }

        return $this->findActivatedCurrencies->forChannel($channelReference);
    }
}
