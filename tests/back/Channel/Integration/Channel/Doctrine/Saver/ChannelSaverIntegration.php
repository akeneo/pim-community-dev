<?php

declare(strict_types=1);

namespace AkeneoTest\Pim\Channel\Integration\Channel\Doctrine\Query;

use Akeneo\Channel\Component\Model\ChannelInterface;
use Akeneo\Channel\Component\Repository\ChannelRepositoryInterface;
use Akeneo\Test\Integration\Configuration;
use Akeneo\Test\Integration\TestCase;
use Akeneo\Tool\Component\StorageUtils\Factory\SimpleFactoryInterface;
use Akeneo\Tool\Component\StorageUtils\Saver\SaverInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

/**
 * @author Paul Chasle <paul.chasle@akeneo.com>
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 * @license http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
final class ChannelSaverIntegration extends TestCase
{
    public function test_that_it_saves_a_new_channel(): void
    {
        $channel = $this->updateChannel(
            $this->getFactory()->create(),
            [
                'code' => 'mobile',
                'locales' => ['en_US'],
                'category_tree' => 'master',
                'currencies' => ['USD'],
            ]
        );
        $createdNormalizedChannel = $this->getNormalizer()->normalize($channel);

        $this->getSaver()->save($channel);
        $savedNormalizedChannel = $this->getNormalizer()->normalize(
            $this->getRepository()->findOneByIdentifier('mobile')
        );

        self::assertEquals($createdNormalizedChannel, $savedNormalizedChannel);
    }

    public function test_that_it_saves_an_existing_channel(): void
    {
        $channel = $this->getRepository()->findOneByIdentifier('ecommerce');
        $notUpdatedNormalizedChannel = $this->getNormalizer()->normalize($channel);

        $channel = $this->updateChannel($channel, ['locales' => ['fr_FR']]);
        $updatedNormalizedChannel = $this->getNormalizer()->normalize($channel);

        $this->getSaver()->save($channel);
        $savedNormalizedChannel = $this->getNormalizer()->normalize(
            $this->getRepository()->findOneByIdentifier('ecommerce')
        );

        self::assertNotEquals($notUpdatedNormalizedChannel, $savedNormalizedChannel);
        self::assertEquals($updatedNormalizedChannel, $savedNormalizedChannel);
    }

    protected function getConfiguration(): Configuration
    {
        return $this->catalog->useMinimalCatalog();
    }

    private function updateChannel(ChannelInterface $channel, array $data): ChannelInterface
    {
        $updater = $this->get('pim_catalog.updater.channel');
        $validator = $this->get('validator');

        $updater->update($channel, $data);
        $violations = $validator->validate($channel);
        if (count($violations) > 0) {
            throw new \InvalidArgumentException((string)$violations);
        }

        return $channel;
    }

    private function getFactory(): SimpleFactoryInterface
    {
        return $this->get('pim_catalog.factory.channel');
    }

    private function getRepository(): ChannelRepositoryInterface
    {
        return $this->get('pim_catalog.repository.channel');
    }

    private function getNormalizer(): NormalizerInterface
    {
        return $this->get('pim_catalog.normalizer.standard.channel');
    }

    private function getSaver(): SaverInterface
    {
        return $this->get('pim_catalog.saver.channel');
    }
}
