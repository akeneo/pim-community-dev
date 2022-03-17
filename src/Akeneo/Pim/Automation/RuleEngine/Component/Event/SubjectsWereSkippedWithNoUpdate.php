<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2022 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Pim\Automation\RuleEngine\Component\Event;

final class SubjectsWereSkippedWithNoUpdate
{
    public function __construct(private array $skippedSubjects)
    {
    }

    public function getSkippedSubjects(): array
    {
        return $this->skippedSubjects;
    }
}
