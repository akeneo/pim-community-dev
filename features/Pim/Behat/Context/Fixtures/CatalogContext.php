<?php

declare(strict_types=1);

namespace Pim\Behat\Context\Fixtures;

use Behat\Behat\Context\Context;
use Doctrine\Common\Util\ClassUtils;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * @author    Damien Carcel <damien.carcel@akeneo.com>
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class CatalogContext implements Context
{
    /** @var ContainerInterface */
    private $container;

    /**
     * @param ContainerInterface $container
     */
    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    /**
     * @Given /^the minimal catalog$/
     */
    public function theMinimalCatalog(): void
    {
        $this->createLocales(['de_DE', 'en_US', 'fr_FR']);
        $this->createCurrencies(['EUR', 'USD']);
        $this->createCategory([
            'code' => 'default',
            'labels' => [
                'en_US' => 'Master catalog',
                'fr_FR' => 'Catalog principal',
            ],
        ]);

        $this->createChannel([
            'code' => 'ecommerce',
            'labels' => [
                'de_DE' => 'Ecommerce',
                'en_US' => 'Ecommerce',
                'fr_FR' => 'Ecommerce',
            ],
            'currencies' => ['EUR', 'USD'],
            'locales' => ['en_US', 'fr_FR'],
            'category_tree' => 'default',
        ]);

        $this->createRole([
            'role' => 'ROLE_ADMINISTRATOR',
            'label' => 'Administrator',
        ]);
        $this->createGroup('IT support');
        $this->createGroup('All');
        $this->createAdminUser('John Doe', 'admin');

        $this->createAttributeGroup([
            'code' => 'other',
            'sort_order' => 100,
            'labels' => ['en_US' => 'Other'],
        ]);

        $this->createAttribute([
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
        ]);

        $this->createMandatoryJobInstances();
    }

    /**
     * @param string $name
     * @param string $userName
     *
     * @Given /^an admin user "([^"]*)"$/
     */
    public function createAdminUser(string $name, string $userName = ''): void
    {
        list($firstName, $lastName) = explode(' ', $name);

        if ('' === $userName) {
            $userName = $firstName;
        }

        $user = $this->container->get('pim_user.factory.user')->create();
        $this->container->get('pim_user.updater.user')->update($user, [
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
        ]);
        $user->setEnabled(true);
        $this->validate($user);
        $this->container->get('pim_user.saver.user')->save($user);
    }

    /**
     * @param array $data
     */
    private function createRole(array $data): void
    {
        $role = $this->container->get('pim_user.factory.role')->create();
        $this->container->get('pim_user.updater.role')->update($role, $data);

        $this->validate($role);

        $this->container->get('pim_user.saver.role')->save($role);
    }

    /**
     * @param string $name
     */
    private function createGroup(string $name): void
    {
        $group = $this->container->get('pim_user.factory.group')->create();
        $this->container->get('pim_user.updater.group')->update($group, ['name' => $name]);

        $this->validate($group);

        $this->container->get('pim_user.saver.group')->save($group);
    }

    /**
     * @param string[] $localeCodes
     */
    private function createLocales(array $localeCodes): void
    {
        $locales = [];

        foreach ($localeCodes as $localeCode) {
            $locale = $this->container->get('pim_catalog.factory.locale')->create();
            $this->container->get('pim_catalog.updater.locale')->update($locale, ['code' => $localeCode]);
            $this->validate($locale);

            $locales[] = $locale;
        }

        $this->container->get('pim_catalog.saver.locale')->saveAll($locales);
    }

    /**
     * @param string[] $currencyCodes
     */
    private function createCurrencies(array $currencyCodes): void
    {
        $currencies = [];

        foreach ($currencyCodes as $currencyCode) {
            $currency = $this->container->get('pim_catalog.factory.currency')->create();
            $this->container->get('pim_catalog.updater.currency')->update($currency, [
                'code' => $currencyCode,
                'enabled' => true,
            ]);

            $this->validate($currency);

            $currencies[] = $currency;
        }

        $this->container->get('pim_catalog.saver.currency')->saveAll($currencies);
    }

    /**
     * @param array $data
     */
    private function createCategory(array $data): void
    {
        $category = $this->container->get('pim_catalog.factory.category')->create();
        $this->container->get('pim_catalog.updater.category')->update($category, $data);

        $this->validate($category);

        $this->container->get('pim_catalog.saver.category')->save($category);
    }

    /**
     * @param array $data
     */
    private function createChannel(array $data): void
    {
        $channel = $this->container->get('pim_catalog.factory.channel')->create();
        $this->container->get('pim_catalog.updater.channel')->update($channel, $data);

        $this->validate($channel);

        $this->container->get('pim_catalog.saver.channel')->save($channel);
    }

    /**
     * @param array $data
     */
    private function createAttributeGroup(array $data): void
    {
        $attributeGroup = $this->container->get('pim_catalog.factory.attribute_group')->create();
        $this->container->get('pim_catalog.updater.attribute_group')->update($attributeGroup, $data);

        $this->validate($attributeGroup);

        $this->container->get('pim_catalog.saver.attribute_group')->save($attributeGroup);
    }

    /**
     * @param array $data
     */
    private function createAttribute(array $data): void
    {
        $attribute = $this->container->get('pim_catalog.factory.attribute')->create();
        $this->container->get('pim_catalog.updater.attribute')->update($attribute, $data);

        $this->validate($attribute);

        $this->container->get('pim_catalog.saver.attribute')->save($attribute);
    }

    private function createMandatoryJobInstances()
    {
        $jobConfigurations = MandatoryJobs::getConfigurations();
        $jobInstances = [];

        foreach ($jobConfigurations as $configuration) {
            $jobInstance = $this->container->get('pim_connector.factory.job_instance')->create();
            $this->container->get('akeneo_batch.updater.job_instance')->update($jobInstance, $configuration);

            $this->validate($jobInstance);

            $jobInstances[] = $jobInstance;
        }

        $this->container->get('akeneo_batch.saver.job_instance')->saveAll($jobInstances);
    }

    /**
     * @param mixed $object
     *
     * @throws \InvalidArgumentException
     */
    protected function validate($object)
    {
        $violations = $this->container->get('validator')->validate($object);

        if (0 !== $violations->count()) {
            $messages = [];
            foreach ($violations as $violation) {
                $messages[] = $violation->getMessage();
            }

            throw new \InvalidArgumentException(sprintf(
                'Object "%s" is not valid, cf following constraint violations "%s"',
                ClassUtils::getClass($object),
                implode(', ', $messages)
            ));
        }
    }
}
