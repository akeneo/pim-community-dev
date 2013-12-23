<?php

namespace Pim\Bundle\CatalogBundle\Entity;

use JMS\Serializer\Annotation\ExclusionPolicy;
use Pim\Bundle\TranslationBundle\Entity\AbstractTranslation;

/**
 * Variant group translation
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 * @ExclusionPolicy("all")
 */
class GroupTranslation extends AbstractTranslation
{
    /**
     * All required columns are mapped through inherited superclass
     */

    /**
     * Change foreign key to add constraint and work with basic entity
     */
    protected $foreignKey;

    /**
     * @var string $label
     */
    protected $label;

    /**
     * Set label
     *
     * @param string $label
     *
     * @return GroupTranslation
     */
    public function setLabel($label)
    {
        $this->label = $label;

        return $this;
    }

    /**
     * Get the label
     *
     * @return string
     */
    public function getLabel()
    {
        return $this->label;
    }
}
