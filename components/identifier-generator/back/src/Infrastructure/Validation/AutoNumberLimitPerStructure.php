<?php

declare(strict_types=1);

namespace Akeneo\Pim\Automation\IdentifierGenerator\Infrastructure\Validation;

use Symfony\Component\Validator\Constraint;

/**
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license   https://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class AutoNumberLimitPerStructure extends Constraint
{
    public string $message = 'validation.create.auto_number_limit_reached';
    public int $limit = 1;

    public function __construct($options = null, array $groups = null, $payload = null)
    {
        if (null === $options || (is_array($options) && !isset($options['limit']))) {
            $options['limit'] = $this->limit;
        }
        parent::__construct($options, $groups, $payload);
    }

    public function getDefaultOption(): string
    {
        return 'limit';
    }

    /**
     * @inerhitDoc
     */
    public function getTargets(): string
    {
        return self::PROPERTY_CONSTRAINT;
    }
}
