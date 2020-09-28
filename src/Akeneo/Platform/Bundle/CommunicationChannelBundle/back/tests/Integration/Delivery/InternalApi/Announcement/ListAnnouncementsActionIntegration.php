<?php

declare(strict_types=1);

namespace Akeneo\Platform\CommunicationChannel\Test\Integration\Delivery\InternalApi\Announcement;

use Akeneo\Platform\CommunicationChannel\Infrastructure\CommunicationChannel\LocalFilestorage\LocalFilestorageFindAnnouncementItems;
use Akeneo\Platform\CommunicationChannel\Test\Integration\WebTestCase;
use Akeneo\Test\Integration\Configuration;
use PHPUnit\Framework\Assert;
use Symfony\Component\HttpFoundation\Response;

class ListAnnouncementsActionIntegration extends WebTestCase
{
    public function setUp(): void
    {
        parent::setup();

        $this->authenticateAsAdmin();
    }

    public function test_it_can_list_first_paginated_announcements()
    {
        $expectedAnnouncements = json_decode(file_get_contents(dirname(__FILE__) . '/../../../../../Infrastructure/CommunicationChannel/LocalFilestorage/serenity-updates.json'), true);
        $this->client->request(
            'GET',
            '/rest/announcements'
        );
        $content = json_decode($this->client->getResponse()->getContent(), true);

        Assert::assertEquals(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());
        Assert::assertIsArray($content['items']);
        Assert::assertEquals(LocalFilestorageFindAnnouncementItems::LIMIT, count($content['items']));
        $this->assertItemKeys($content['items'][0]);
        $this->assertFirstItem(null, $expectedAnnouncements['data'], $content['items']);
    }

    public function test_it_can_list_paginated_announcements_with_a_search_after_parameter()
    {
        $expectedAnnouncements = json_decode(file_get_contents(dirname(__FILE__) . '/../../../../../Infrastructure/CommunicationChannel/LocalFilestorage/serenity-updates.json'), true);
        $searchAfter = '2e04e7e4-6c55-4cdd-b151-dab34d6a31a4';
        $this->client->request(
            'GET',
            '/rest/announcements',
            [
                'search_after' => $searchAfter
            ]
        );
        $content = json_decode($this->client->getResponse()->getContent(), true);

        Assert::assertEquals(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());
        Assert::assertIsArray($content['items']);
        Assert::assertEquals(LocalFilestorageFindAnnouncementItems::LIMIT, count($content['items']));
        $this->assertFirstItem($searchAfter, $expectedAnnouncements['data'], $content['items']);
    }

    protected function getConfiguration(): Configuration
    {
        return $this->catalog->useMinimalCatalog();
    }

    private function assertItemKeys(array $item): void
    {
        Assert::assertArrayHasKey('id', $item);
        Assert::assertArrayHasKey('title', $item);
        Assert::assertArrayHasKey('description', $item);
        Assert::assertArrayHasKey('img', $item);
        Assert::assertArrayHasKey('altImg', $item);
        Assert::assertArrayHasKey('link', $item);
        Assert::assertArrayHasKey('startDate', $item);
        Assert::assertArrayHasKey('tags', $item);
    }

    private function assertFirstItem(?string $searchAfter, array $expectedAnnouncementItems, array $announcementItems): void
    {
        $index = array_search($searchAfter, array_column($expectedAnnouncementItems, 'id'));
        if (null === $searchAfter) {
            $firstAnnouncement = $expectedAnnouncementItems[$index];
        } else {
            $firstAnnouncement = $expectedAnnouncementItems[$index+1];
        }

        Assert::assertEquals($firstAnnouncement['id'], $announcementItems[0]['id']);
    }
}
