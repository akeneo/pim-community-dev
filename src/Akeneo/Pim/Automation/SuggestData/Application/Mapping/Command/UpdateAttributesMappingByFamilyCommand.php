<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2018 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Pim\Automation\SuggestData\Application\Mapping\Command;

/**
 * @author    Romain Monceau <romain@akeneo.com>
 */
class UpdateAttributesMappingByFamilyCommand
{
    public function __construct(string $familyCode, array $mapping)
    {
        $this->validate($mapping);

        $this->familyCode = $familyCode;
        $this->mapping = $mapping;
    }

    private function validate(array $mapping): void
    {
        var_dump($mapping);
    }
}
