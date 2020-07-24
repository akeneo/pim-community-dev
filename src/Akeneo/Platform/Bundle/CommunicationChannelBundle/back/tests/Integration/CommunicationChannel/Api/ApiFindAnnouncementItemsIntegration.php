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

class ApiFindAnnouncementItemsIntegration extends KernelTestCase
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

    public function test_it_finds_first_page_of_announcements()
    {
        $query = self::$container->get('akeneo_communication_channel.query.api.find_announcement_items');
        $result = $query->byPimVersion('Serenity', '2020105', null, 10);
        Assert::assertCount(10, $result);
        Assert::assertEquals(
            new AnnouncementItem(
                'update_1-duplicate-a-product_2020-07',
                'New: duplicate a product',
                'You want to easily create a new product based on an existing one? Use our brand new duplicate feature to copy a product in your user interface in one single click!',
                '/bundles/akeneocommunicationchannel/images/announcements/zoom-on-duplicate-action.png',
                'New duplicate action',
                'https://help.akeneo.com/pim/serenity/updates/2020-06.html#new-duplicate-a-product',
                new \DateTimeImmutable('2020-07-05'),
                new \DateTimeImmutable('2020-07-14'),
                ['updates']
            ),
            $result[0]
        );
    }

    public function test_it_finds_second_page_of_announcements()
    {
        $currentDir = __DIR__ . '/Expectations';
        $process = new Process("./vendor/bin/phiremock -p 8088 -i 0.0.0.0 -e '$currentDir'");
        $process->start();
        $this->waitServerUp();

        $query = self::$container->get('akeneo_communication_channel.query.api.find_announcement_items');
        $result = $query->byPimVersion('Serenity', '2020105', 'update_1-new-screen-for-measurements-families_2020-05', 10);
        Assert::assertCount(1, $result);
        Assert::assertEquals(
            new AnnouncementItem(
                'update_2-new-measurements-api-endpoints_2020-05',
                'New endpoints to manage measurements',
                'We introduced two new API endpoints to create, update and list your measurement families.',
                '/bundles/akeneocommunicationchannel/images/announcements/measurements-api.png',
                'Measurement endpoints',
                'https://help.akeneo.com/pim/serenity/updates/2020-04.html#new-endpoints-to-manage-measurements',
                new \DateTimeImmutable('2020-05-07'),
                new \DateTimeImmutable('2020-05-14'),
                ['updates']
            ),
            $result[0]
        );
    }

    public function waitServerUp()
    {
        $attempt = 0;
        do {
            try {
                $httpClient = new Client(['base_uri' => self::$container->getParameter('comm_panel_api_url')]);
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
