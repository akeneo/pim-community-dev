<?php
declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2021 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Pim\TableAttribute\Domain\TableConfiguration\Validation;

use Webmozart\Assert\Assert;

final class RequiredForCompletenessValidation implements TableValidation
{
    public const KEY = 'required_for_completeness';

    private bool $requiredForCompleteness;

    private function __construct(bool $requiredForCompleteness)
    {
        $this->requiredForCompleteness = $requiredForCompleteness;
    }

    public static function fromValue($requiredFromCompleteness): TableValidation
    {
        Assert::boolean($requiredFromCompleteness);

        return new self($requiredFromCompleteness);
    }

    public function getValue(): bool
    {
        return $this->requiredForCompleteness;
    }
}
