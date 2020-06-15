<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Domain\ErrorManagement\Model\ValueObject\Documentation;

/**
 * @author    Willy Mesnage <willy.mesnage@akeneo.com>
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class Documentation
{
    const TYPE_TEXT = 'text';
    const TYPE_INFORMATION = 'information';

    /** @var string */
    private $message;

    /** @var array<string, MessageParameterInterface> */
    private $messageParameters;

    /** @var self::TYPE_* */
    private $type;

    /**
     * @param string $message Could include parameters with the pattern {needle}.
     * @param array<string, MessageParameterInterface> $messageParameters Must have as many parameters as {needle} in message.
     * @param self::TYPE_* $type Type of the documentation.
     */
    public function __construct(string $message, array $messageParameters, string $type)
    {
        $this->message = $message;

        foreach ($messageParameters as $needle => $messageParameter) {
            if (!$messageParameter instanceof MessageParameterInterface) {
                throw new \InvalidArgumentException(sprintf(
                    'Class "%s" accepts only associative array of "%s" as $messageParameters.',
                    self::class,
                    MessageParameterInterface::class
                ));
            }
            if (1 !== substr_count($message, sprintf('{%s}', $needle))) {
                throw new \InvalidArgumentException(sprintf(
                    '$messageParameters "%s" not found in $message "%s".',
                    $needle,
                    $message
                ));
            }
        }
        $this->messageParameters = $messageParameters;

        switch ($type) {
            case self::TYPE_TEXT:
            case self::TYPE_INFORMATION:
                $this->type = $type;
                break;
            default:
                throw new \InvalidArgumentException('Documentation $type is not valid.');
        }
    }

    /**
     * @return array{
     *  message: string,
     *  parameters: array<string, array<string, string|array>>,
     *  type: self::TYPE_*
     * }
     */
    public function normalize(): array
    {
        $normalizedParams = [];
        foreach ($this->messageParameters as $needle => $messageParameter) {
            $normalizedParams[$needle] = $messageParameter->normalize();
        }

        return [
            'message' => $this->message,
            'parameters' => $normalizedParams,
            'type' => $this->type
        ];
    }
}
