<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2019 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Pim\Automation\FranklinInsights\Infrastructure\Connector\JobLauncher;

use Akeneo\Pim\Automation\FranklinInsights\Application\Mapping\Service\IdentifyProductsToResubscribeInterface;

/**
 * @author Mathias METAYER <mathias.metayer@akeneo.com>
 */
class InMemoryIdentifyProductsToResubscribe implements IdentifyProductsToResubscribeInterface
{
    /** @var array */
    private $updatedIdentifiers = [];

    /**
     * @param array $franklinIdentifierCodes
     */
    public function process(array $franklinIdentifierCodes): void
    {
        $this->updatedIdentifiers = $franklinIdentifierCodes;
    }

    /**
     * @return array
     */
    public function updatedIdentifierCodes(): array
    {
        return $this->updatedIdentifiers;
    }
}
