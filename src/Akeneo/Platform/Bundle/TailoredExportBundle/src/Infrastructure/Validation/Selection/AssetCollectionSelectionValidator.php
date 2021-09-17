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

namespace Akeneo\Platform\TailoredExport\Infrastructure\Validation\Selection;

use Akeneo\Platform\TailoredExport\Infrastructure\Validation\ChannelShouldExist;
use Akeneo\Platform\TailoredExport\Infrastructure\Validation\LocaleShouldBeActive;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\Constraints\Choice;
use Symfony\Component\Validator\Constraints\Collection;
use Symfony\Component\Validator\Constraints\EqualTo;
use Symfony\Component\Validator\Constraints\IsNull;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Optional;
use Symfony\Component\Validator\Constraints\Required;
use Symfony\Component\Validator\Constraints\Type;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\ConstraintViolationListInterface;

class AssetCollectionSelectionValidator extends ConstraintValidator
{
    /** @var string[] */
    private array $availableCollectionSeparator;

    public function __construct(array $availableCollectionSeparator)
    {
        $this->availableCollectionSeparator = $availableCollectionSeparator;
    }

    public function validate($selection, Constraint $constraint)
    {
        $validator = $this->context->getValidator();
        $violations = $validator->validate($selection, [
            new Collection(
                [
                    'fields' => [
                        'type' => new Choice(
                            [
                                'choices' => [
                                    'code',
                                    'label',
                                    'media_file',
                                    'media_link'
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
                        'property' => new Optional([new Type(['type' => 'string'])]),
                        'with_prefix_and_suffix' => new Optional([new Type(['type' => 'bool'])])
                    ],
                ]
            ),
        ]);

        if (0 < $violations->count()) {
            $this->buildViolations($violations);

            return;
        }

        if ('label' === $selection['type']) {
            $violations = $validator->validate($selection['locale'] ?? null, [
                new NotBlank(),
                new LocaleShouldBeActive()
            ]);
            $this->buildViolations($violations, '[locale]');
        } elseif ('media_file' === $selection['type'] || 'media_link' === $selection['type']) {
            $localeIsRequiredViolations = $validator->validate($selection, new Collection(['fields' => ['locale' => new Required()], 'allowExtraFields' => true]));
            if (0 < $localeIsRequiredViolations->count()) {
                $this->buildViolations($localeIsRequiredViolations);
            } elseif (null !== $selection['locale']) {
                $localeShouldBeActiveViolations = $validator->validate($selection['locale'], [new LocaleShouldBeActive()]);
                $this->buildViolations($localeShouldBeActiveViolations, '[locale]');
            }
            $channelIsRequiredViolations = $validator->validate($selection, new Collection(['fields' => ['channel' => new Required()], 'allowExtraFields' => true]));
            if (0 < $channelIsRequiredViolations->count()) {
                $this->buildViolations($channelIsRequiredViolations);
            } elseif (null !== $selection['channel']) {
                $channelShouldExistViolations = $validator->validate($selection['channel'], [new ChannelShouldExist()]);
                $this->buildViolations($channelShouldExistViolations, '[channel]');
            }
            if ('media_file' === $selection['type']) {
                $propertyViolations = $validator->validate($selection, new Collection(['fields' => ['property' => new Required([new EqualTo('file_key')])], 'allowExtraFields' => true]));
                $this->buildViolations($propertyViolations);
            } elseif ('media_link' === $selection['type']) {
                $withSuffixAndPrefixViolations = $validator->validate($selection, new Collection(['fields' => ['with_prefix_and_suffix' => new Required()], 'allowExtraFields' => true]));
                $this->buildViolations($withSuffixAndPrefixViolations);
            }
        }
    }

    private function buildViolations(ConstraintViolationListInterface $violations, ?string $path = null)
    {
        foreach ($violations as $violation) {
            $this->context->buildViolation(
                $violation->getMessage(),
                $violation->getParameters()
            )
                ->atPath($path ?? $violation->getPropertyPath())
                ->addViolation();
        }
    }
}
