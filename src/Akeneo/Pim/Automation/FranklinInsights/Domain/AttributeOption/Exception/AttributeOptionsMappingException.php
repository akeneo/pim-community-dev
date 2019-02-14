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

namespace Akeneo\Pim\Automation\FranklinInsights\Domain\AttributeOption\Exception;

/**
 * @author Romain Monceau <romain@akeneo.com>
 */
class AttributeOptionsMappingException extends \Exception
{
    /** @var string */
    private const CONSTRAINT_KEY = 'akeneo_franklin_insights.entity.attribute_options_mapping.constraint.%s';

    /** @var array */
    private $messageParams;

    /**
     * @param string $message
     * @param array $messageParams
     */
    public function __construct(string $message, array $messageParams = [])
    {
        parent::__construct($message);

        $this->messageParams = $messageParams;
    }

    /**
     * @return AttributeOptionsMappingException
     */
    public static function emptyAttributeOptionsMapping(): self
    {
        $message = sprintf(static::CONSTRAINT_KEY, 'no_attribute_options_mapped');

        return new static($message, []);
    }
}
