<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2016 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PimEnterprise\Component\Catalog\Updater;

use Akeneo\Component\StorageUtils\Updater\ObjectUpdaterInterface;
use Pim\Component\Catalog\Model\AttributeInterface;

/**
 * Enterprise decorator of the Community AttributeUpdater
 *
 * @author Arnaud Langlade <arnaud.langlade@akeneo.com>
 */
class AttributeUpdater implements ObjectUpdaterInterface
{
    /** @var ObjectUpdaterInterface */
    protected $attributeUpdater;

    /** @var array */
    protected $properties;

    /**
     * @param ObjectUpdaterInterface $objectUpdater
     * @param array                  $properties
     */
    public function __construct(ObjectUpdaterInterface $objectUpdater, array $properties)
    {
        $this->attributeUpdater = $objectUpdater;
        $this->properties = $properties;
    }

    /**
     * {@inheritdoc}
     *
     * @param AttributeInterface $attribute
     */
    public function update($attribute, array $data, array $options = [])
    {
        $filteredData = [];
        foreach ($data as $field => $value) {
            if (in_array($field, $this->properties)) {
                $attribute->setProperty($field, $value);
            } else {
                $filteredData[$field] = $value;
            }
        }
        
        $this->attributeUpdater->update($attribute, $filteredData, $options);

        return $this;
    }
}
