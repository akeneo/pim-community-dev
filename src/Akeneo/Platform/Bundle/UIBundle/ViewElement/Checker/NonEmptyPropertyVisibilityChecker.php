<?php

namespace Akeneo\Platform\Bundle\UIBundle\ViewElement\Checker;

use Symfony\Component\PropertyAccess\Exception\NoSuchPropertyException;
use Symfony\Component\PropertyAccess\PropertyAccess;

/**
 * Displays a view element only if a specific property exists in the context and is not null
 *
 * @author    Filips Alpe <filips@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class NonEmptyPropertyVisibilityChecker implements VisibilityCheckerInterface
{
    /**
     * {@inheritdoc}
     */
    public function isVisible(array $config = [], array $context = [])
    {
        if (!isset($config['property'])) {
            throw new \InvalidArgumentException('The "property" should be provided in the configuration.');
        }

        $accessor = PropertyAccess::createPropertyAccessor();

        try {
            $value = $accessor->getValue($context, $config['property']);
        } catch (NoSuchPropertyException $e) {
            $value = null;
        }

        return null !== $value;
    }
}
