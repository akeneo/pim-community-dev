<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2018 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Pim\Automation\SuggestData\Domain\Common\Exception;

/**
 * @author Damien Carcel <damien.carcel@akeneo.com>
 */
final class DataProviderException extends \Exception
{
    private const CONSTRAINT_KEY = 'akeneo_suggest_data.entity.data_provider.constraint.%s';

    /** @var array */
    private $messageParams;

    /**
     * @param string $message
     * @param array $messageParams
     * @param int $code
     * @param \Exception $previous
     */
    public function __construct(
        string $message,
        array $messageParams,
        int $code,
        \Exception $previous
    ) {
        parent::__construct($message, $code, $previous);

        $this->messageParams = $messageParams;
    }

    /**
     * @param \Exception $previousException
     *
     * @return DataProviderException
     */
    public static function serverIsDown(\Exception $previousException): self
    {
        $message = sprintf(self::CONSTRAINT_KEY, 'ask_franklin_down');

        return new static($message, [], 500, $previousException);
    }

    /**
     * @return array
     */
    public function getMessageParams(): array
    {
        return $this->messageParams;
    }
}
