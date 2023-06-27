<?php

declare(strict_types=1);

namespace Akeneo\Pim\Structure\Component\Validator\Constraints;

use Symfony\Component\Validator\Constraint;

/**
 * @copyright 2023 Akeneo SAS (https://www.akeneo.com)
 * @license   https://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class IdentifierAttributeCreationLimit extends Constraint
{
    public string $message = 'pim_catalog.constraint.identifier_attribute_limit_reached';

    public int $limit = 1;

    public function __construct($options = null, array $groups = null, $payload = null)
    {
        if (null === $options || (\is_array($options) && !isset($options['limit']))) {
            $options['limit'] = $this->limit;
        }
        parent::__construct($options, $groups, $payload);
    }

    public function getDefaultOption()
    {
        return 'limit';
    }

    /**
     * @inerhitDoc
     */
    public function getTargets(): string
    {
        return self::CLASS_CONSTRAINT;
    }
}
