<?php

namespace AkeneoTestEnterprise\Pim\Permission\Integration\Persistence\Sql\LocaleRight;

use Akeneo\Channel\Infrastructure\Component\Query\PublicApi\Permission\GetAllViewableLocalesForUserInterface;
use Akeneo\Test\Integration\Configuration;
use Akeneo\Test\Integration\TestCase;
use PHPUnit\Framework\Assert;

class GetAllViewableLocalesForUserIntegration extends TestCase
{
    /**
     * @test
     */
    public function it_returns_all_viewable_locales()
    {
        $query = $this->getQuery();

        $userId =  $this->get('database_connection')
            ->fetchColumn('SELECT id FROM oro_user WHERE username = "mary"', [], 0);

        Assert::assertSame(['en_US', 'fr_FR', 'zh_CN'], $query->fetchAll($userId));
    }

    /**
     * {@inheritdoc}
     */
    protected function getConfiguration(): Configuration
    {
        return $this->catalog->useTechnicalCatalog(featureFlags: ['permission']);
    }

    private function getQuery(): GetAllViewableLocalesForUserInterface
    {
        return $this->get('pimee_security.query.get_all_viewable_locales_for_user');
    }
}
