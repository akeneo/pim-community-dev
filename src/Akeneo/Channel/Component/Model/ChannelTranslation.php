<?php

namespace Akeneo\Channel\Component\Model;

use Akeneo\Tool\Component\Localization\Model\AbstractTranslation;

/**
 * Category translation entity
 *
 * @author    Philippe Mossière <philippe.mossiere@akeneo.com>
 * @copyright 2016 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ChannelTranslation extends AbstractTranslation implements ChannelTranslationInterface
{
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
