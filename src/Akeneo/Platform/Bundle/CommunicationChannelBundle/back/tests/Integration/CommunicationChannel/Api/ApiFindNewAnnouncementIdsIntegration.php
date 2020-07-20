<?php

declare(strict_types=1);

namespace Akeneo\Platform\CommunicationChannel\Test\Integration\Delivery\InternalApi\Announcement;

use Akeneo\Platform\CommunicationChannel\Domain\Announcement\Model\Read\AnnouncementItem;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Exception\ConnectException;
use PHPUnit\Framework\Assert;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Process\Process;

class ApiFindNewAnnouncementIdsIntegration extends KernelTestCase
{

    /** @var Process */
    private $process;

    public function setUp(): void
    {
        parent::setUp();
        static::bootKernel(['debug' => false]);

        $currentDir = __DIR__ . '/Expectations';
        $this->process = new Process("./vendor/bin/phiremock -p 8088 -i 0.0.0.0 -e '$currentDir'");
        $this->process->start();
        $this->waitServerUp();
    }

    public function tearDown(): void
    {
        parent::tearDown();

        $this->process->stop();
    }

    public function test_it_finds_new_announcement_ids()
    {
        $query = self::$container->get('akeneo_communication_channel.query.api.find_new_announcement_ids');
        $result = $query->find('Serenity', '2020105');
        Assert::assertCount(4, $result);
        Assert::assertEquals(
            [
                'update_1-duplicate-a-product_2020-07',
                'update_2-option-screen-revamp_2020-07',
                'update_3-rules-updates_2020-07',
                'update_4-manually-execute-naming-conventions-on-assets_2020-07'
            ],
            $result
        );
    }

    public function waitServerUp()
    {
        $attempt = 0;
        do {
            try {
                $httpClient = new Client(['base_uri' => self::$container->getParameter('help_center_api_url')]);
                $httpClient->get('/');
            } catch (ConnectException $e) {
                usleep(100000);
            } catch (ClientException $e) {
                return; // started
            }

            $attempt++;
        } while ($attempt < 30);

        throw new \RuntimeException('Impossible to start the mock HTTP server.');
    }
}
