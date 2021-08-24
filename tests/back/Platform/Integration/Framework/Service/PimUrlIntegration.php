<?php

declare(strict_types=1);

namespace AkeneoTest\Platform\Integration\Framework\Service;

use Akeneo\Platform\Bundle\FrameworkBundle\Service\PimUrl;
use Akeneo\Test\Integration\TestCase;
use PHPUnit\Framework\Assert;

/**
 * @copyright 2021 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class PimUrlIntegration extends TestCase
{
    private const DEFAULT_AKENEO_PIM_URL_IN_DOTENV = 'http://localhost:8080';

    private PimUrl $pimUrl;

    /**
     * {@inheritdoc}
     */
    protected function getConfiguration()
    {
        return $this->catalog->useMinimalCatalog();
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->pimUrl = $this->get('pim_framework.service.pim_url');
    }

    public function test_pim_url_is_returned()
    {
        Assert::assertEquals(self::DEFAULT_AKENEO_PIM_URL_IN_DOTENV, $this->pimUrl->getPimUrl());
    }
}
