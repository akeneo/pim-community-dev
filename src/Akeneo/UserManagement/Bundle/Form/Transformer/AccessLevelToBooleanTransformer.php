<?php

namespace Akeneo\UserManagement\Bundle\Form\Transformer;

use Oro\Bundle\SecurityBundle\Acl\AccessLevel;
use Symfony\Component\Form\DataTransformerInterface;

/**
 * Transforms access levels into boolean values and vice versa
 *
 * @author    Filips Alpe <filips@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class AccessLevelToBooleanTransformer implements DataTransformerInterface
{
    /**
     * {@inheritdoc}
     */
    public function transform($value)
    {
        if (null === $value) {
            return '';
        }

        return $value === AccessLevel::SYSTEM_LEVEL ? true : false;
    }

    /**
     * {@inheritdoc}
     */
    public function reverseTransform($value)
    {
        if ('' === $value) {
            return null;
        }

        return $value === true ? AccessLevel::SYSTEM_LEVEL : AccessLevel::NONE_LEVEL;
    }
}
