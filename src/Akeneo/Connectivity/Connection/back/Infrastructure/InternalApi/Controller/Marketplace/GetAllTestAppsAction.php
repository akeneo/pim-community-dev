<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Infrastructure\InternalApi\Controller\Marketplace;

use Symfony\Component\HttpFoundation\JsonResponse;

/**
 * @copyright 2021 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class GetAllTestAppsAction
{
    public function __invoke()
    {
        return new JsonResponse([
            'total' => 1,
            'apps' => [
                [
                    'id' => '90741597-54c5-48a1-98da-a68e7ee0a715',
                    'name' => 'Akeneo Shopware 6 Connector by EIKONA Media',
                    'logo' => 'https://marketplace.akeneo.com/sites/default/files/styles/extension_logo_large/public/extension-logos/akeneo-to-shopware6-eimed_0.jpg?itok=InguS-1N',
                    'author' => 'EIKONA Media GmbH',
                    'partner' => 'Akeneo Preferred Partner',
                    'description' => 'With the new "Akeneo-Shopware-6-Connector" from EIKONA Media, you can smoothly export all your product data from Akeneo to Shopware. The connector uses the standard interfaces provided for data exchange. Benefit from up-to-date product data in all your e-commerce channels and be faster on the market.',
                    'url' => 'https://marketplace.akeneo.com/extension/akeneo-shopware-6-connector-eikona-media?utm_medium=pim&utm_content=extension_link&utm_source=http%3A%2F%2Flocalhost%3A8080',
                    'categories' => [],
                    'certified' => false,
                    'activate_url' => 'http://shopware.example.com/activate?pim_url=http%3A%2F%2Flocalhost%3A8080',
                    'callback_url' => 'http://shopware.example.com/callback',
                    'connected' => false,
                ]
            ]
        ]);
    }
}
