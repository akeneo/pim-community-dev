<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Domain\ErrorManagement\Model\Read;

/**
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
final class BusinessError
{
    public function __construct(private string $connectionCode, private \DateTimeImmutable $dateTime, private string $content)
    {
    }

    /**
     * @return array{
     *  connection_code: string,
     *  date_time: string,
     *  content: string
     * }
     */
    public function normalize(): array
    {
        return [
            'connection_code' => $this->connectionCode,
            'date_time' => $this->dateTime->format(\DateTimeInterface::ATOM),
            'content' => \json_decode($this->content, true, 512, JSON_THROW_ON_ERROR)
        ];
    }
}
