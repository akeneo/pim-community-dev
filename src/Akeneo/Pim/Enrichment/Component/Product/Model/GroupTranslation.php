<?php

namespace Akeneo\Pim\Enrichment\Component\Product\Model;

use Akeneo\Tool\Component\Localization\Model\AbstractTranslation;

/**
 * Group translation
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class GroupTranslation extends AbstractTranslation implements GroupTranslationInterface
{
    /** All required columns are mapped through inherited superclass */

    /** Change foreign key to add constraint and work with basic entity */
    protected $foreignKey;

    /** @var string */
    protected $label;

    /**
     * {@inheritdoc}
     */
    public function setLabel($label)
    {
        $this->label = $label;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getLabel()
    {
        return $this->label;
    }
}
