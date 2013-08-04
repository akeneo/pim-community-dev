<?php

namespace Pim\Bundle\InstallerBundle\DataFixtures\ORM;

use Symfony\Component\Yaml\Yaml;
use Doctrine\Common\Persistence\ObjectManager;
use Pim\Bundle\ConfigBundle\Entity\Channel;

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
            $channel = $this->createChannel($data['code'], $data['label'], $data['locales']);
            $manager->persist($channel);
        }

        $manager->flush();
    }

    /**
     * Create a channel
     * @param string $code    Channel code
     * @param string $name    Channel name
     * @param array  $locales Activated locales
     *
     * @return \Pim\Bundle\ConfigBundle\Entity\Channel
     */
    protected function createChannel($code, $name, $locales)
    {
        $channel = new Channel();
        $channel->setCode($code);
        $channel->setName($name);
        foreach ($locales as $locale) {
            $channel->addLocale($this->getReference('locale.'.$locale));
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
