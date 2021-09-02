<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Tests\Integration\Mock;

use Akeneo\Connectivity\Connection\Infrastructure\Marketplace\WebMarketplaceApiInterface;

/**
 * @copyright 2021 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class FakeWebMarketplaceApi implements WebMarketplaceApiInterface
{
    private array $extensions = [];
    private array $apps = [
        [
            'id' => '90741597-54c5-48a1-98da-a68e7ee0a715',
            'name' => 'Akeneo Shopware 6 Connector by EIKONA Media',
            'logo' => 'https://marketplace.akeneo.com/sites/default/files/styles/extension_logo_large/public/extension-logos/akeneo-to-shopware6-eimed_0.jpg?itok=InguS-1N',
            'author' => 'EIKONA Media GmbH',
            'partner' => 'Akeneo Preferred Partner',
            'description' => 'With the new "Akeneo-Shopware-6-Connector" from EIKONA Media, you can smoothly export all your product data from Akeneo to Shopware. The connector uses the standard interfaces provided for data exchange. Benefit from up-to-date product data in all your e-commerce channels and be faster on the market.',
            'url' => 'https://marketplace.akeneo.com/extension/akeneo-shopware-6-connector-eikona-media',
            'categories' => [
                'E-commerce',
            ],
            'certified' => false,
            'activate_url' => 'http://shopware.example.com/activate',
            'callback_url' => 'http://shopware.example.com/callback',
        ],
        [
            'id' => 'b18561ff-378e-41a5-babb-ca0ec0af569a',
            'name' => 'Akeneo PIM Connector for Shopify',
            'logo' => 'https://marketplace.akeneo.com/sites/default/files/styles/extension_logo_large/public/extension-logos/shopify-connector-logo-1200x.png?itok=mASOVlwC',
            'author' => 'StrikeTru',
            'partner' => 'Akeneo Partner',
            'description' => 'SaaS software from StrikeTru that seamlessly connects Akeneo PIM to the Shopify platform. It allows Shopify users to quickly setup a link to Akeneo PIM and sync all product catalog data to Shopify within minutes. It eliminates a lot of manual and repetitive work involved in updating the product catalog of a Shopify store. You can send and receive products, variations, modifiers, categories, standard and custom attributes, images and more from Akeneo PIM into your Shopify store. Compatible with all Akeneo PIM editions – Community, Growth, Enterprise (On-Premise, Cloud Flexibility, and Cloud Serenity) and StrikeTru\'s smallPIM.',
            'url' => 'https://marketplace.akeneo.com/extension/akeneo-pim-connector-shopify',
            'categories' => [
                'E-commerce',
            ],
            'certified' => false,
            'activate_url' => 'http://shopify.example.com/activate',
            'callback_url' => 'http://shopify.example.com/callback',
        ],
    ];

    /**
     * @param array<array{
     *      id: string,
     *      name: string,
     *      logo: string,
     *      author: string,
     *      partner?: string,
     *      description: string,
     *      url: string,
     *      categories: array<string>,
     *      certified?: bool,
     * }> $extensions
     */
    public function setExtensions(array $extensions): void
    {
        $this->extensions = $extensions;
    }

    public function getExtensions(int $offset = 0, int $limit = 10): array
    {
        return [
            'total' => count($this->extensions),
            'offset' => $offset,
            'limit' => $limit,
            'items' => array_slice($this->extensions, $offset, $limit),
        ];
    }

    /**
     * @param array<array{
     *      id: string,
     *      name: string,
     *      logo: string,
     *      author: string,
     *      partner?: string,
     *      description: string,
     *      url: string,
     *      categories: array<string>,
     *      certified?: bool,
     *      activate_url: string,
     *      callback_url: string,
     * }> $extensions
     */
    public function setApps(array $apps): void
    {
        $this->apps = $apps;
    }

    public function getApps(int $offset = 0, int $limit = 10): array
    {
        return [
            'total' => count($this->apps),
            'offset' => $offset,
            'limit' => $limit,
            'items' => array_slice($this->apps, $offset, $limit),
        ];
    }

    public function getApp(string $id): ?array
    {
        return array_filter($this->apps, function (array $app) use ($id) {
            return $app['id'] === $id;
        })[0] ?? null;
    }
}
