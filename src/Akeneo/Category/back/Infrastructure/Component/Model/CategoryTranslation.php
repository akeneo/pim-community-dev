<?php

namespace Akeneo\Category\Infrastructure\Component\Model;

use Akeneo\Tool\Component\Localization\Model\AbstractTranslation;

/**
 * Category translation entity.
 *
 * @author    Romain Monceau <romain@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class CategoryTranslation extends AbstractTranslation implements CategoryTranslationInterface
{
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

    /**
     * @param CategoryInterface $foreignKey
     */
    public function setForeignKey($foreignKey): self
    {
        $this->foreignKey = $foreignKey;

        return $this;
    }

    public function getForeignKey(): CategoryInterface
    {
        return $this->foreignKey;
    }
}
