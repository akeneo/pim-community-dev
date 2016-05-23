<?php

namespace Pim\Bundle\ImportExportBundle\Form\DataTransformer;

use Pim\Component\Catalog\Query\Filter\Operators;
use Symfony\Component\Form\DataTransformerInterface;

/**
 * @author    Marie Bochu <marie.bochu@akeneo.com>
 * @copyright 2016 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class BooleanToPBQTransformer implements DataTransformerInterface
{
    /**
     * {@inheritdoc}
     */
    public function transform($value)
    {
        switch ((string) $value['value']) {
            case '1':
                $status = 'enabled';
                break;
            case '0':
                $status = 'disabled';
                break;
            default:
                $status = 'all';
                break;
        }

        return $status;
    }

    /**
     * {@inheritdoc}
     */
    public function reverseTransform($value)
    {
        switch ($value) {
            case 'enabled':
                $status = true;
                break;
            case 'disabled':
                $status = false;
                break;
            default:
                $status = null;
        }

        return [
            'operator' => Operators::EQUALS,
            'value'    => $status,
            'context'  => []
        ];
    }
}
