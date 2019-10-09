<?php

namespace AkeneoTest\Pim\Enrichment\Integration\Storage\Sql;

use Akeneo\Channel\Component\Model\Channel;
use Akeneo\Test\Integration\TestCase;
use PHPUnit\Framework\Assert;

/**
 * @author    Arnaud Langlade <arnaud.langlade@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class FindActivatedCurrenciesIntegration extends TestCase
{
    public function testThatItReturnsTheActivatedCurrencyOfAChannel()
    {
        $this->assertSameWithoutOrder(
            $this->get('pim_catalog.query.find_activated_currencies')->forChannel('ecommerce'),
            ['USD']
        );
    }

    public function testThatItReturnsAnEmptyArrayForUnkownChannels()
    {
        Assert::assertEmpty(
            $this->get('pim_catalog.query.find_activated_currencies')->forChannel('unknown_channel')
        );
    }

    public function testReturnsMultipleActivatedCurrenciesForAChannel()
    {
        $this->addEURToEcommerce();
        $this->assertSameWithoutOrder(
            $this->get('pim_catalog.query.find_activated_currencies')->forChannel('ecommerce'),
            ['USD', 'EUR']
        );
    }

    public function testReturnsAllActivatedCurrenciesForAllChannels()
    {
        $this->addAdditionalCurrenciesToMobile();
        $this->assertSameWithoutOrder(
            $this->get('pim_catalog.query.find_activated_currencies')->forAllChannels('ecommerce'),
            ['USD', 'EUR', 'ADP', 'AFA']
        );
    }

    /**
     * {@inheritdoc}
     */
    protected function getConfiguration()
    {
        return $this->catalog->useMinimalCatalog();
    }

    private function addEURToEcommerce(): void
    {
        $eur = $this->get('pim_catalog.repository.currency')->findOneByIdentifier('EUR');
        $channelRepository = $this->get('pim_catalog.repository.channel');
        $ecommerce = $channelRepository->findOneBy(['code' => 'ecommerce']);
        $ecommerce->addCurrency($eur);
        $this->get('pim_catalog.saver.channel')->save($ecommerce);
    }

    private function addAdditionalCurrenciesToMobile()
    {
        $eur = $this->get('pim_catalog.repository.currency')->findOneByIdentifier('EUR');
        $adp = $this->get('pim_catalog.repository.currency')->findOneByIdentifier('ADP');
        $afa = $this->get('pim_catalog.repository.currency')->findOneByIdentifier('AFA');
        $adp->setActivated(true);
        $afa->setActivated(true);
        $this->get('pim_catalog.saver.currency')->saveAll([$adp, $afa]);

        $master = $this->get('pim_catalog.repository.category')->findOneByIdentifier('master');

        $mobile = new Channel();
        $mobile->setCode('mobile');
        $mobile->setCurrencies([$eur, $adp, $afa]);
        $mobile->setCategory($master);
        $this->get('pim_catalog.saver.channel')->save($mobile);
    }

    private function assertSameWithoutOrder(array $expected, array $actual): void
    {
        Assert::assertEqualsCanonicalizing($expected, $actual);
    }
}
