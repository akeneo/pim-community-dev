<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Domain\ErrorManagement\Model\Write;

use Akeneo\Connectivity\Connection\Domain\ErrorManagement\Model\ValueObject\ErrorType;
use Akeneo\Connectivity\Connection\Domain\Settings\Model\ValueObject\ConnectionCode;

/**
 * @author    Willy Mesnage <willy.mesnage@akeneo.com>
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
abstract class ApiError implements ApiErrorInterface
{
    /** @var string */
    private $content;

    /** @var \DateTimeImmutable */
    private $dateTime;

    public function __construct(string $content, \DateTimeImmutable $dateTime = null)
    {
        try {
            $decoded = json_decode($content, true, 512, JSON_THROW_ON_ERROR);
        } catch (\JsonException $jsonException) {
            throw new \InvalidArgumentException(
                'The content of the API error must be in JSON format.',
                0,
                $jsonException
            );
        }
        if (empty($decoded)) {
            throw new \InvalidArgumentException(
                'The API error must have a content, but you provided en empty json.'
            );
        }

        if (null === $dateTime) {
            $dateTime = new \DateTimeImmutable('now', new \DateTimeZone('UTC'));
        }

        $this->content = $content;
        $this->dateTime = $dateTime;
    }

    public function content(): string
    {
        return $this->content;
    }

    abstract public function type(): ErrorType;

    public function normalize(): array
    {
        return [
            'content' => json_decode($this->content(), true),
            'error_datetime' => $this->dateTime->format(\DateTimeInterface::ATOM),
        ];
    }
}
