<?php

declare(strict_types=1);

namespace Akeneo\Test\IntegrationTestsBundle\Configuration;

use Akeneo\Test\Integration\Configuration;
use Akeneo\Test\IntegrationTestsBundle\Path;

/**
 * @author    Alexandre Hocquard <alexandre.hocquard@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class Catalog implements CatalogInterface
{
    /**
     * {@inheritdoc}
     */
    public function useTechnicalSqlCatalog(): Configuration
    {
        $catalogDirectories = [
            (string) new Path('back', 'Integration', 'catalog','technical_sql'),
        ];

        $fixtureDirectories = [
            $this->getTechnicalFixtures(),
            $this->getReferenceDataFixtures()
        ];

        return new Configuration($catalogDirectories, $fixtureDirectories);
    }

    /**
     * {@inheritdoc}
     */
    public function useTechnicalCatalog(): Configuration
    {
        $catalogDirectories = [
            (string) new Path('back', 'Integration', 'catalog','technical'),
        ];

        $fixtureDirectories = [
            $this->getTechnicalFixtures(),
            $this->getReferenceDataFixtures()
        ];

        return new Configuration($catalogDirectories, $fixtureDirectories);
    }

    /**
     * {@inheritdoc}
     */
    public function useMinimalCatalog(): Configuration
    {
        $catalogDirectories = [
            (string) new Path('..', 'src', 'Pim', 'Bundle', 'InstallerBundle', 'Resources', 'fixtures', 'minimal'),
        ];

        $fixtureDirectories = [
            $this->getReferenceDataFixtures(),
            $this->getTechnicalFixtures(),
        ];

        return new Configuration($catalogDirectories, $fixtureDirectories);
    }

    /**
     * {@inheritdoc}
     */
    public function useFunctionalCatalog(string $catalog): Configuration
    {
        $catalogDirectories = [
            (string) new Path('..', 'features', 'Context', 'catalog', $catalog),
        ];

        $fixtureDirectories = [
            (string) new Path('..', 'features', 'Context'),
            $this->getReferenceDataFixtures()
        ];

        return new Configuration($catalogDirectories, $fixtureDirectories);
    }

    /**
     * @return string
     *
     * @throws \Exception
     */
    private function getReferenceDataFixtures(): string
    {
        return (string) new Path('..', 'src', 'Acme', 'Bundle', 'AppBundle', 'Resources', 'fixtures');
    }

    /**
     * @return string
     *
     * @throws \Exception
     */
    private function getTechnicalFixtures(): string
    {
        return (string) new Path('back', 'Integration', 'fixtures');
    }
}
