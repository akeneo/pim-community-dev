<?php

namespace Pim\Bundle\InstallerBundle\DataFixtures\ORM;

use Symfony\Component\Yaml\Yaml;
use Doctrine\Common\Persistence\ObjectManager;
use Pim\Bundle\CatalogBundle\Entity\Channel;

/**
 * Load fixtures for channels
 *
 * @author    Romain Monceau <romain@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class LoadChannelData extends AbstractInstallerFixture
{
    /**
     * {@inheritdoc}
     */
    public function load(ObjectManager $manager)
    {
        $configuration = Yaml::parse(realpath($this->getFilePath()));

        foreach ($configuration['channels'] as $data) {
            $channel = $this->createChannel($data['code'], $data['label'], $data['locales'], $data['currencies'], $data['tree']);
            $manager->persist($channel);
        }

        $manager->flush();
    }

    /**
     * Create a channel
     * @param string   $code       Channel code
     * @param string   $name       Channel name
     * @param string[] $locales    Locales
     * @param string[] $currencies Currencies
     * @param string   $tree       Category tree
     *
     * @return \Pim\Bundle\CatalogBundle\Entity\Channel
     */
    protected function createChannel($code, $name, $locales, $currencies, $tree)
    {
        $channel = new Channel();
        $channel->setCode($code);
        $channel->setName($name);
        $channel->setCategory($this->getReference('category.'.$tree));
        foreach ($locales as $locale) {
            $channel->addLocale($this->getReference('locale.'.$locale));
        }
        foreach ($currencies as $currency) {
            $channel->addCurrency($this->getReference('currency.'.$currency));
        }
        $this->setReference('channel.'. $code, $channel);

        return $channel;
    }

    /**
     * {@inheritdoc}
     */
    public function getEntity()
    {
        return 'channels';
    }

    /**
     * {@inheritdoc}
     */
    public function getOrder()
    {
        return 3;
    }
}
