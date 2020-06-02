<?php
declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Component\Error\Documented;

/**
 * @author    Willy Mesnage <willy.mesnage@akeneo.com>
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class Documentation
{
    /** @var string */
    private $message;

    /** @var MessageParameterInterface[] */
    private $messageParameters;

    /**
     * @param string $message Could include parameters with the pattern {needle}.
     * @param MessageParameterInterface[] $messageParameters Must have as many parameters as {needle} in message.
     */
    public function __construct(string $message, array $messageParameters)
    {
        $this->message = $message;
        foreach ($messageParameters as $messageParameter) {
            if (!$messageParameter instanceof MessageParameterInterface) {
                throw new \InvalidArgumentException(sprintf(
                    'Class "%s" accepts only array of "%s" as $messageParameters.',
                        self::class,
                        MessageParameterInterface::class
                    )
                );
            }
            if (1 !== substr_count($message, sprintf('%s', $messageParameter->needle()))) {
                throw new \InvalidArgumentException(sprintf(
                        '$messageParameters "%s" not found in $message "%s".',
                        $messageParameter->needle(),
                        $message
                    )
                );
            }
        }
        $this->messageParameters = $messageParameters;
    }

    public function normalize(): array
    {
        $normalizedParams = [];
        foreach ($this->messageParameters as $messageParameter) {
            $normalizedParams[$messageParameter->needle()] = $messageParameter->normalize();
        }

        return [
            'message' => $this->message,
            'parameters' => $normalizedParams
        ];
    }
}
