<?php

declare(strict_types=1);

namespace Akeneo\Platform\CommunicationChannel\Infrastructure\CommunicationChannel\Api;

use Akeneo\Platform\CommunicationChannel\Domain\Announcement\Query\FindNewAnnouncementIdsInterface;
use GuzzleHttp\Client;

/**
 * @author Christophe Chausseray <chausseray.christophe@gmail.com>
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
final class ApiFindNewAnnouncementIds implements FindNewAnnouncementIdsInterface
{
    private const BASE_URI = '/new_announcements';

    /** @var Client */
    private $client;

    public function __construct(string $apiUrl)
    {
        $this->client = new Client(['base_uri' => $apiUrl]);
    }

    public function find(): array
    {
        $response = $this->client->request('GET', self::BASE_URI);
        $content = json_decode((string) $response->getBody(), true);

        return $content;
    }
}
