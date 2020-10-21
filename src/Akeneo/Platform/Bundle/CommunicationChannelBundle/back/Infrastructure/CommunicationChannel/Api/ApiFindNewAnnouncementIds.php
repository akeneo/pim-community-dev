<?php

declare(strict_types=1);

namespace Akeneo\Platform\Bundle\CommunicationChannelBundle\back\Infrastructure\CommunicationChannel\Api;

use Akeneo\Platform\Bundle\CommunicationChannelBundle\back\Domain\Announcement\Query\FindNewAnnouncementIdsInterface;

/**
 * @author Christophe Chausseray <chausseray.christophe@gmail.com>
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
final class ApiFindNewAnnouncementIds implements FindNewAnnouncementIdsInterface
{
    private const BASE_URI = '/new_announcements';

    /** @var string */
    private $url;

    public function __construct(string $apiUrl)
    {
        $this->url = $apiUrl . self::BASE_URI;
    }

    public function find(string $pimEdition, string $pimVersion, string $locale): array
    {
        $queryParameters = [
            'pim_edition' => $pimEdition,
            'pim_version' => $pimVersion,
            'locale' => $locale,
        ];

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

        return json_decode((string) $body, true);
    }
}
