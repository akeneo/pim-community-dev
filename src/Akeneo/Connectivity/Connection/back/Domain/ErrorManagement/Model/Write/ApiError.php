<?php
declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Domain\ErrorManagement\Model\Write;

use Akeneo\Connectivity\Connection\Domain\ErrorManagement\Model\ValueObject\ErrorType;
use Ramsey\Uuid\Uuid;

/**
 * @author    Willy Mesnage <willy.mesnage@akeneo.com>
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
abstract class ApiError implements ApiErrorInterface
{
    private string $id;
    private string $content;
    private \DateTimeImmutable $dateTime;

    public function __construct(string $content, \DateTimeImmutable $dateTime = null)
    {
        try {
            $decoded = \json_decode($content, true, 512, JSON_THROW_ON_ERROR);
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

        $this->id = Uuid::uuid4()->toString();
        $this->content = $content;
        $this->dateTime = $dateTime;
    }

    public function id(): string
    {
        return  $this->id;
    }

    public function content(): string
    {
        return $this->content;
    }

    abstract public function type(): ErrorType;

    /**
     * @return array{id: string, content: mixed, error_datetime: string}
     */
    public function normalize(): array
    {
        return [
            'id' => $this->id(),
            'content' => \json_decode($this->content(), true, 512, JSON_THROW_ON_ERROR),
            'error_datetime' => $this->dateTime->format(\DateTimeInterface::ATOM),
        ];
    }
}
