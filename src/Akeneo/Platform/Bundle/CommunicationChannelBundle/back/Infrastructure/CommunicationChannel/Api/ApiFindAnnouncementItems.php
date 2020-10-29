<?php

declare(strict_types=1);

namespace Akeneo\Platform\Bundle\CommunicationChannelBundle\back\Infrastructure\CommunicationChannel\Api;

use Akeneo\Platform\Bundle\CommunicationChannelBundle\back\Domain\Announcement\Model\Read\AnnouncementItem;
use Akeneo\Platform\Bundle\CommunicationChannelBundle\back\Domain\Announcement\Query\FindAnnouncementItemsInterface;

/**
 * @author Christophe Chausseray <chausseray.christophe@gmail.com>
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
final class ApiFindAnnouncementItems implements FindAnnouncementItemsInterface
{
    private const BASE_URI = '/announcements';

    /** @var string */
    private $url;

    public function __construct(string $apiUrl)
    {
        $this->url = $apiUrl . self::BASE_URI;
    }

    public function byPimVersion(string $pimEdition, string $pimVersion, string $locale, ?string $searchAfter): array
    {
        $queryParameters = [
            'pim_edition' => $pimEdition,
            'pim_version' => $pimVersion,
            'locale' => $locale,
        ];
        if (null !== $searchAfter) {
            $queryParameters['search_after'] = $searchAfter;
        }

        $queryParameters = \http_build_query($queryParameters, '', '&');
        $url =$this->url . '?' . $queryParameters;
        $body = \file_get_contents($url, false, \stream_context_create(['http' => ['ignore_errors' => true]]));

        $statusHeader = $http_response_header[0];
        preg_match('#HTTP/[0-9\.]+\s+([0-9]+)#', $statusHeader, $match);
        $httpStatusCode = (int) $match[1];

        if ($httpStatusCode !== 200) {
            throw new \RuntimeException(
                sprintf(
                    'Error occurred when fetching the announcements with status code "%s". Please check the logs of the external service.',
                    $httpStatusCode
                )
            );
        }

        $content = \json_decode($body, true)['data'];

        return array_map(function ($announcement) {
            return $this->getAnnouncementItem($announcement);
        }, array_values($content));
    }

    private function getAnnouncementItem(array $announcement): AnnouncementItem
    {
        return new AnnouncementItem(
            $announcement['id'],
            $announcement['title'],
            $announcement['description'],
            $announcement['img'] ?? null,
            $announcement['imgAlt'] ?? null,
            $announcement['link'],
            new \DateTimeImmutable($announcement['startDate']),
            new \DateTimeImmutable($announcement['notificationEndDate']),
            $announcement['tags']
        );
    }
}
