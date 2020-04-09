<?php
declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Domain\ErrorManagement\Model\Write;

use Akeneo\Connectivity\Connection\Domain\Settings\Model\ValueObject\ConnectionCode;

/**
 * @author    Willy Mesnage <willy.mesnage@akeneo.com>
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class BusinessError
{
    /** @var ConnectionCode */
    private $connectionCode;

    /** @var string */
    private $content;

    public function __construct(string $connectionCode, string $content)
    {
        try {
            $decoded = json_decode($content, true, 512, JSON_THROW_ON_ERROR);
        } catch (\JsonException $jsonException) {
            throw new \InvalidArgumentException(
                'The content of the business error must be in JSON format.',
                0,
                $jsonException
            );
        }
        if (empty($decoded)) {
            throw new \InvalidArgumentException(
                'The business error must have a content, but you provided en empty json.'
            );
        }
        $this->content = $content;
        $this->connectionCode = new ConnectionCode($connectionCode);
    }

    public function connectionCode(): ConnectionCode
    {
        return $this->connectionCode;
    }

    public function content(): string
    {
        return $this->content;
    }
}
