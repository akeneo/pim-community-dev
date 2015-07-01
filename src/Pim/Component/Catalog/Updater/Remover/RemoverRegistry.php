<?php

namespace Pim\Component\Catalog\Updater\Remover;

use Pim\Bundle\CatalogBundle\Model\AttributeInterface;

/**
 * Registry of removers
 *
 * @author    Willy Mesnage <willy.mesnage@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class RemoverRegistry implements RemoverRegistryInterface
{
    /** @var AttributeRemoverInterface[] priorized attribute removers */
    protected $attributeRemovers = [];

    /** @var FieldRemoverInterface[] priorized field removers */
    protected $fieldRemovers = [];

    /**
     * {@inheritdoc}
     */
    public function register(RemoverInterface $remover)
    {
        if ($remover instanceof FieldRemoverInterface) {
            $this->fieldRemovers[] = $remover;
        }
        if ($remover instanceof AttributeRemoverInterface) {
            $this->attributeRemovers[] = $remover;
        }

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getFieldRemover($field)
    {
        foreach ($this->fieldRemovers as $remover) {
            if ($remover->supportsField($field)) {
                return $remover;
            }
        }

        return null;
    }

    /**
     * {@inheritdoc}
     */
    public function getAttributeRemover(AttributeInterface $attribute)
    {
        foreach ($this->attributeRemovers as $remover) {
            if ($remover->supportsAttribute($attribute)) {
                return $remover;
            }
        }

        return null;
    }
}
