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

namespace Akeneo\Platform\Syndication\Infrastructure\Validation;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\ConstraintValidator;

/**
 * @copyright 2021 Akeneo SAS (https://www.akeneo.com)
 */
final class ProductFiltersValidator extends ConstraintValidator
{
    /**
     * {@inheritdoc}
     */
    public function validate($value, Constraint $constraint): void
    {
        if (null === $value) {
            return;
        }

        if (!$constraint instanceof ProductFilters) {
            throw new \InvalidArgumentException('Invalid constraint');
        }

        foreach ($value as $filter) {
            $this->context
                ->getValidator()
                ->inContext($this->context)
                ->atPath(sprintf('[%s]', $filter['field']))
                ->validate($filter, new Assert\Collection(
                    [
                        'fields' => [
                            'field' => new Assert\NotBlank(),
                            'operator' => new Assert\NotBlank(),
                            'context' => new Assert\Optional([
                                new Assert\Collection([
                                    'fields' => [
                                        'scope' => new Assert\Optional(new ChannelShouldExist()),
                                        'locale' => new Assert\Optional(),
                                        'channel' => new Assert\Optional(new ChannelShouldExist())
                                    ],
                                ]),
                            ]),
                        ],
                        'allowExtraFields' => true,
                    ]
                ));
        }
    }
}
