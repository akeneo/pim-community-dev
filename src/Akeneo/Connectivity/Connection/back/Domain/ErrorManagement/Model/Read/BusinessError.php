<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Domain\ErrorManagement\Model\Read;

/**
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
final class BusinessError
{
    /** @var string */
    private $connectionCode;

    /** @var \DateTimeImmutable */
    private $dateTime;

    /** @var string */
    private $content;

    public function __construct(string $connectionCode, \DateTimeImmutable $dateTime, string $content)
    {
        $this->connectionCode = $connectionCode;
        $this->dateTime = $dateTime;
        $this->content = $content;
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
            'content' => json_decode($this->content, true)
        ];
    }
}
