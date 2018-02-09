<?php

declare(strict_types=1);

namespace Pim\Behat\Context\Fixture;

use Behat\Behat\Context\Context;

/**
 * @author    Damien Carcel <damien.carcel@akeneo.com>
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class CatalogContext implements Context
{
    private $groups;
    private $roles;
    private $users;

    private $attributes;
    private $attributeGroups;
    private $categories;
    private $channels;
    private $currencies;
    private $jobInstances;
    private $locales;

    private $roleRepository;
    private $groupRepository;

    public function __construct(
        EntityLoader $groups,
        EntityLoader $roles,
        EntityLoader $users,
        EntityLoader $attributes,
        EntityLoader $attributeGroups,
        EntityLoader $categories,
        EntityLoader $channels,
        EntityLoader $currencies,
        EntityLoader $jobInstances,
        EntityLoader $locales
    ) {
        $this->groups = $groups;
        $this->roles = $roles;
        $this->users = $users;
        $this->attributes = $attributes;
        $this->attributeGroups = $attributeGroups;
        $this->categories = $categories;
        $this->channels = $channels;
        $this->currencies = $currencies;
        $this->jobInstances = $jobInstances;
        $this->locales = $locales;
    }

    /**
     * @Given /^the minimal catalog$/
     */
    public function theMinimalCatalog(): void
    {
        $this->locales->load([['code' => 'de_DE'], ['code' => 'en_US'], ['code' => 'fr_FR']]);
        $this->currencies->load([['code' => 'EUR', 'enabled' => true], ['code' => 'USD', 'enabled' => true]]);
        $this->categories->load([[
            'code' => 'default',
            'labels' => [
                'en_US' => 'Master catalog',
                'fr_FR' => 'Catalog principal',
            ],
        ]]);

        $this->channels->load([[
            'code' => 'ecommerce',
            'labels' => [
                'de_DE' => 'Ecommerce',
                'en_US' => 'Ecommerce',
                'fr_FR' => 'Ecommerce',
            ],
            'currencies' => ['EUR', 'USD'],
            'locales' => ['en_US', 'fr_FR'],
            'category_tree' => 'default',
        ]]);

        $this->roles->load([[
            'role' => 'ROLE_ADMINISTRATOR',
            'label' => 'Administrator',
        ]]);
        $this->groups->load([['name' => 'IT support']]);
        $this->groups->load([['name' => 'All']]);
        $this->createAdminUser('John Doe', 'admin');

        $this->attributeGroups->load([[
            'code' => 'other',
            'sort_order' => 100,
            'labels' => ['en_US' => 'Other'],
        ]]);

        $this->attributes->load([[
            'code' => 'sku',
            'labels' => [
                'en_US' => 'SKU',
                'fr_FR' => 'SKU',
            ],
            'type' => 'pim_catalog_identifier',
            'group' => 'other',
            'required' => true,
            'unique' => true,
            'useable_as_grid_filter' => true,
            'sort_order' => 1,
        ]]);

        $this->jobInstances->load(MandatoryJobs::getConfigurations());
    }

    /**
     * @Given /^the administrator "([^"]*)"$/
     */
    public function createAdminUser(string $name, string $userName = ''): void
    {
        list($firstName, $lastName) = explode(' ', $name);

        if ('' === $userName) {
            $userName = $firstName;
        }

        $this->users->load([[
            'username' => $userName,
            'first_name' => $firstName,
            'last_name' => $lastName,
            'email' => $userName.'@example.com',
            'password' => $userName,
            'catalog_locale' => 'en_US',
            'user_locale' => 'en_US',
            'catalog_scope' => 'ecommerce',
            'default_tree' => 'default',
            'roles' => ['ROLE_ADMINISTRATOR'],
            'groups' => ['IT support'],
            'enabled' => true,
        ]]);
    }

    /**
     * @Given /^the manager "([^"]*)"$/
     */
    public function createManagerUser(string $name, string $userName = ''): void
    {
        list($firstName, $lastName) = explode(' ', $name);

        if ('' === $userName) {
            $userName = $firstName;
        }

        $this->users->load([[
            'username' => $userName,
            'first_name' => $firstName,
            'last_name' => $lastName,
            'email' => $userName.'@example.com',
            'password' => $userName,
            'catalog_locale' => 'en_US',
            'user_locale' => 'en_US',
            'catalog_scope' => 'ecommerce',
            'default_tree' => 'default',
            'roles' => ['ROLE_MANAGER'],
            'groups' => ['Manager'],
            'enabled' => true,
        ]]);
    }
}
