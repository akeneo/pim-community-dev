<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2016 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PimEnterprise\Component\Security\Connector\ArrayConverter\Flat;

use Pim\Component\Connector\ArrayConverter\FieldsRequirementValidator;
use Pim\Component\Connector\ArrayConverter\StandardArrayConverterInterface;

/**
 * Locale Access Flat to Standard format converter
 *
 * @author Pierre Allard <pierre.allard@akeneo.com>
 */
class LocaleAccessStandardConverter implements StandardArrayConverterInterface
{
    /** @var FieldsRequirementValidator */
    protected $validator;

    /**
     * @param FieldsRequirementValidator $validator
     */
    public function __construct(FieldsRequirementValidator $validator)
    {
        $this->validator = $validator;
    }

    /**
     * {@inheritdoc}
     *
     * Converts flat csv array to standard structured array:
     *
     * Before:
     * [
     *      'locale'        => 'en_US',
     *      'view_products' => 'All',
     *      'edit_products' => 'IT support,Manager',
     * ]
     *
     * After:
     * [
     *      'locale'        => 'en_US',
     *      'view_products' => ['All'],
     *      'edit_products' => ['IT support', 'Manager'],
     * ]
     */
    public function convert(array $item, array $options = [])
    {
        $this->validator->validateFields($item, ['locale']);

        $convertedItem = [];
        foreach ($item as $field => $data) {
            $convertedItem = $this->convertField($convertedItem, $field, $data);
        }

        return $convertedItem;
    }

    /**
     * @param array  $convertedItem
     * @param string $field
     * @param mixed  $data
     *
     * @return array
     */
    protected function convertField(array $convertedItem, $field, $data)
    {
        if ('locale' === $field) {
            $convertedItem[$field] = $data;
        } else {
            $convertedItem[$field] = explode(',', $data);
        }

        return $convertedItem;
    }
}
