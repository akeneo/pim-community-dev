<?php

declare(strict_types=1);

namespace Akeneo\Catalogs\Infrastructure\Validation\ProductValueFilters;

use Akeneo\Catalogs\Application\Persistence\Channel\GetChannelQueryInterface;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

/**
 * @author    Willy Mesnage <willy.mesnage@akeneo.com>
 * @copyright 2022 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 * @psalm-suppress PropertyNotSetInConstructor
 */
final class FilterContainsValidChannelValidator extends ConstraintValidator
{
    public function __construct(
        private GetChannelQueryInterface $getChannelQuery,
    ) {
    }

    public function validate($value, Constraint $constraint): void
    {
        if (!$constraint instanceof FilterContainsValidChannel) {
            throw new UnexpectedTypeException($constraint, FilterContainsValidChannel::class);
        }

        if (!\is_string($value) || '' === $value) {
            return;
        }

        $channel = $this->getChannelQuery->execute($value);

        if (null === $channel) {
            $this->context
                ->buildViolation(
                    'akeneo_catalogs.validation.product_value_filters.channel.unknown',
                    ['{{ channel_name }}' => $value],
                )
                ->addViolation();
        }
    }
}
