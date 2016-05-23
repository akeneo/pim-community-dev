<?php

namespace Pim\Bundle\ImportExportBundle\Form\DataTransformer;

use Pim\Component\Catalog\Query\Filter\Operators;
use Symfony\Component\Form\DataTransformerInterface;

/**
 * @author    Marie Bochu <marie.bochu@akeneo.com>
 * @copyright 2016 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class UpdatedToPBQTransformer implements DataTransformerInterface
{
    /**
     * {@inheritdoc}
     */
    public function transform($value)
    {
        if (null === $value) {
            return 'all';
        }

        return 'last_export';
    }

    /**
     * {@inheritdoc}
     */
    public function reverseTransform($value)
    {
        if ('all' === $value) {
            return null;
        }

        return [
            'operator' => Operators::GREATER_THAN,
            'value'    => 'last_export',
            'context'  => []
        ];
    }
}
