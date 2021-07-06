<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Infrastructure\Marketplace;

use Akeneo\Connectivity\Connection\Domain\Marketplace\GetAllExtensionsQueryInterface;
use Symfony\Component\Finder\SplFileInfo;

/**
 * @copyright 2021 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class GetAllExtensionsQuery implements GetAllExtensionsQueryInterface
{
    public function execute(): array
    {
        return [
            'total' => 2,
            'extensions' => [
                [
                    'id' => '6fec7055-36ad-4301-9889-46c46ddd446a',
                    'name' => 'Extension 1',
                    'logo' => 'https://marketplace.test/logo/extension_1.png',
                    'author' => 'Partner 1',
                    'partner' => 'Akeneo Partner',
                    'description' => 'Our Akeneo Connector',
                    'url' => 'https://marketplace.test/extension/extension_1',
                    'categories' => ['E-commerce'],
                    'certified' => false
                ],
                [
                    'id' => '896ae911-e877-46a0-b7c3-d7c572fe39ed',
                    'name' => 'Extension 2',
                    'logo' => 'https://marketplace.test/logo/extension_2.png',
                    'author' => 'Partner 2',
                    'partner' => 'Akeneo Preferred Partner',
                    'description' => 'Our Akeneo Connector',
                    'url' => 'https://marketplace.test/extension/extension_2',
                    'categories' => ['E-commerce', 'Print'],
                    'certified' => true
                ]
            ],
        ];
    }
}
