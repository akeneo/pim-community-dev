<?php

namespace AkeneoTestEnterprise\Pim\Permission\Integration\Persistence\Sql\LocaleRight;

use Akeneo\Test\Integration\Configuration;
use Akeneo\Test\Integration\TestCase;
use PHPUnit\Framework\Assert;
use Akeneo\Pim\Permission\Bundle\Persistence\Sql\LocaleRight\IsValueViewableByUserForLocale;
use Akeneo\Pim\Enrichment\Component\Product\Value\ScalarValue;

class IsValueViewableByUserForLocaleIntegration extends TestCase
{
    /**
     * A user can view a locale if they are in at least one group that has view access on the locale
     *
     * @test
     */
    public function it_returns_true_when_a_user_can_view_a_value_for_a_locale()
    {
        $query = $this->getQuery();

        $value = ScalarValue::localizableValue('nothing', 'whatever', 'en_US');
        $userId =  $this->get('database_connection')
                        ->fetchColumn('SELECT id FROM oro_user WHERE username = "mary"', [], 0);

        $isViewable = $query->isViewable($value, $userId);

        Assert::assertTrue($isViewable);
    }

    /**
     * A user can not view a locale if they are not in any group that has view access on the locale
     *
     * @test
     */
    public function it_returns_false_when_a_user_can_not_view_a_value_for_a_locale()
    {
        $query = $this->getQuery();

        $value = ScalarValue::localizableValue('nothing', 'whatever', 'de_DE');
        $userId =  $this->get('database_connection')
                        ->fetchColumn('SELECT id FROM oro_user WHERE username = "mary"', [], 0);

        $isViewable = $query->isViewable($value, $userId);

        Assert::assertFalse($isViewable);
    }

    /**
     * The value is viewable when it is not localizable
     *
     * @test
     */
    public function it_returns_true_when_a_value_is_not_localizable()
    {
        $query = $this->getQuery();

        $value = ScalarValue::value('nonlocalizable', 'nothing');
        $userId =  $this->get('database_connection')
            ->fetchColumn('SELECT id FROM oro_user WHERE username = "mary"', [], 0);

        $isViewable = $query->isViewable($value, $userId);

        Assert::assertTrue($isViewable);
    }

    /**
     * {@inheritdoc}
     */
    protected function getConfiguration(): Configuration
    {
        return $this->catalog->useTechnicalCatalog();
    }

    private function getQuery(): IsValueViewableByUserForLocale
    {
        return $this->get('pimee_security.query.is_value_viewable_by_user_for_locale');
    }
}
