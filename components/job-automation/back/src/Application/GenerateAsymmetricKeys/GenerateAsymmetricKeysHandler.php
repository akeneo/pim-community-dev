<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2022 Akeneo SAS (https://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Platform\JobAutomation\Application\GenerateAsymmetricKeys;

use Akeneo\Platform\JobAutomation\Domain\AsymmetricKeysGeneratorInterface;
use Akeneo\Platform\JobAutomation\Domain\Query\SaveAsymmetricKeysQueryInterface;

class GenerateAsymmetricKeysHandler
{
    public function __construct(
        private readonly AsymmetricKeysGeneratorInterface $asymmetricKeysGenerator,
        private readonly SaveAsymmetricKeysQueryInterface $saveAsymmetricKeysQuery,
    ) {
    }

    public function handle(): void
    {
        $keys = $this->asymmetricKeysGenerator->generate();
        $this->saveAsymmetricKeysQuery->execute($keys);
    }
}
