<?php

declare(strict_types=1);

namespace Akeneo\Platform\CommunicationChannel\EndToEnd\Announcement;

use Akeneo\Platform\CommunicationChannel\Test\EndToEnd\WebTestCase;
use PHPUnit\Framework\Assert;
use Symfony\Component\HttpFoundation\Response;
use Akeneo\Test\Integration\Configuration;

class ListAnnouncementEndToEnd extends WebTestCase
{
    public function setUp(): void
    {
        parent::setup();
    }

    public function test_it_can_list_announcements()
    {
        $this->client->request(
            'GET',
            '/rest/announcements'
        );

        Assert::assertEquals(Response::HTTP_NO_CONTENT, $this->client->getResponse()->getStatusCode());
    }

    protected function getConfiguration(): Configuration
    {
        return $this->catalog->useMinimalCatalog();
    }

}
