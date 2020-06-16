<?php

declare(strict_types=1);

namespace Akeneo\Platform\CommunicationChannel\Integration\Announcement;

use Akeneo\Platform\CommunicationChannel\Test\Integration\WebTestCase;
use PHPUnit\Framework\Assert;
use Symfony\Component\HttpFoundation\Response;
use Akeneo\Test\Integration\Configuration;

class ListAnnouncementIntegration extends WebTestCase
{
    public function setUp(): void
    {
        parent::setup();
    }

    public function test_it_can_list_all_announcements()
    {
        $expectedJson = json_decode(file_get_contents(dirname(__FILE__) . '/../../../Infrastructure/CommunicationChannel/InMemory/serenity-updates.json'), true);
        $this->client->request(
            'GET',
            '/rest/announcements'
        );
        $content = json_decode($this->client->getResponse()->getContent(), true);

        Assert::assertEquals(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());
        Assert::assertEquals(count($expectedJson['data']), count($content['items']));
    }

    protected function getConfiguration(): Configuration
    {
        return $this->catalog->useMinimalCatalog();
    }

}
