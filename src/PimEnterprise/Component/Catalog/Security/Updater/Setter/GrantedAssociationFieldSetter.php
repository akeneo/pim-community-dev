<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2017 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PimEnterprise\Component\Catalog\Security\Updater\Setter;

use Pim\Component\Catalog\Updater\Setter\AbstractFieldSetter;
use Pim\Component\Catalog\Updater\Setter\FieldSetterInterface;
use PimEnterprise\Component\Security\Exception\ResourceAccessDeniedException;

/**
 * Check if product associated is at least "viewable" to be associated to a product

 * @author Marie Bochu <marie.bochu@akeneo.com>
 */
class GrantedAssociationFieldSetter extends AbstractFieldSetter implements FieldSetterInterface
{
    /** @var FieldSetterInterface */
    private $associationFieldSetter;

    /**
     * @param FieldSetterInterface $categoryFieldSetter
     * @param array                $supportedFields
     */
    public function __construct(
        FieldSetterInterface $categoryFieldSetter,
        array $supportedFields
    ) {
        $this->associationFieldSetter = $categoryFieldSetter;
        $this->supportedFields = $supportedFields;
    }

    /**
     * {@inheritdoc}
     */
    public function setFieldData($product, $field, $data, array $options = [])
    {
        try {
            $this->associationFieldSetter->setFieldData($product, $field, $data, $options);
        } catch (ResourceAccessDeniedException $e) {
            throw new ResourceAccessDeniedException(
                $e->getResource(),
                'You cannot associate a product on which you have not a view permission.',
                $e
            );
        }
    }
}
