<?php

namespace Pim\Bundle\EnrichBundle\ViewElement;

use Symfony\Component\PropertyAccess\Exception\NoSuchPropertyException;
use Symfony\Component\PropertyAccess\PropertyAccess;

/**
 * Displays a view element only in an edit form context
 *
 * @author    Julien Janvier <julien.janvier@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class EditFormVisibilityChecker implements ViewElementVisibilityCheckerInterface
{
    /** @staticvar string */
    const PATH = '[form].vars[value].id';

    /**
     * {@inheritdoc}
     */
    public function isVisible(array $context = [])
    {
        $accessor = PropertyAccess::createPropertyAccessor();

        try {
            $value = $accessor->getValue($context, static::PATH);
        } catch (NoSuchPropertyException $e) {
            $value = null;
        }

        return null !== $value;
    }
}
