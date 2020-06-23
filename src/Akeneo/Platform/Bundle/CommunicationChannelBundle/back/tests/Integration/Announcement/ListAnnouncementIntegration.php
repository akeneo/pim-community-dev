<?php

declare(strict_types=1);

namespace Akeneo\Platform\CommunicationChannel\Test\Integration\Announcement;

use Akeneo\Platform\CommunicationChannel\Test\Integration\WebTestCase;
use Akeneo\Test\Integration\Configuration;
use PHPUnit\Framework\Assert;
use Symfony\Component\HttpFoundation\Response;

class ListAnnouncementIntegration extends WebTestCase
{
    public function setUp(): void
    {
        parent::setup();

        $this->authenticateAsAdmin();
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
        Assert::assertIsArray($content['items']);
        Assert::assertEquals(count($expectedJson['data']), count($content['items']));
        $this->assertItemKeys($content['items'][0]);
    }

    protected function getConfiguration(): Configuration
    {
        return $this->catalog->useMinimalCatalog();
    }

    private function assertItemKeys(array $item): void
    {
        Assert::assertArrayHasKey('title', $item);
        Assert::assertArrayHasKey('description', $item);
        Assert::assertArrayHasKey('img', $item);
        Assert::assertArrayHasKey('altImg', $item);
        Assert::assertArrayHasKey('link', $item);
        Assert::assertArrayHasKey('startDate', $item);
        Assert::assertArrayHasKey('tags', $item);
    }
}
