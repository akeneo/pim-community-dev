<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2020 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Test\Pim\Automation\RuleEngine\Integration\Context;

use Akeneo\Test\Integration\Configuration;
use Akeneo\Test\IntegrationTestsBundle\Configuration\CatalogInterface;
use AkeneoEnterprise\Test\IntegrationTestsBundle\Loader\FixturesLoader;
use Behat\Behat\Context\Context;

/**
 * @author    Nicolas Marniesse <nicolas.marniesse@akeneo.com>
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 */
final class CatalogConfigurationContext implements Context
{
    /** @var CatalogInterface */
    private $catalog;

    /** @var FixturesLoader */
    private $fixturesLoader;

    /** @var string */
    private $projectDir;

    public function __construct(CatalogInterface $catalog, FixturesLoader $fixturesLoader, string $projectDir)
    {
        $this->catalog = $catalog;
        $this->fixturesLoader = $fixturesLoader;
        $this->projectDir = $projectDir;
    }

    /**
     * @Given /^(?:a|an|the) "([^"]*)" catalog configuration$/
     */
    public function loadCatalog(string $catalog): void
    {
        try {
            $configuration = $this->catalog->useFunctionalCatalog($catalog);
        } catch (\Exception $e) {
            $configuration = new Configuration([
                $this->projectDir . '/tests/legacy/features/Context/catalog/' . $catalog,
            ]);
        }

        $this->fixturesLoader->load($configuration);
    }
}
