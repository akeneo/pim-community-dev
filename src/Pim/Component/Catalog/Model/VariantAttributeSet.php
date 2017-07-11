<?php

namespace Pim\Component\Catalog\Model;

use Doctrine\Common\Collections\ArrayCollection;

/**
 * @author    Arnaud Langlade <arnaud.langlade@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class VariantAttributeSet implements VariantAttributeSetInterface
{
    /** @var int */
    private $level;

    /** @var ArrayCollection */
    private $attributes;

    /** @var ArrayCollection */
    private $axes;

    /**
     * {@inheritdoc}
     */
    public function getLevel(): int
    {
        return $this->level;
    }

    /**
     * {@inheritdoc}
     */
    public function setLevel(int $level)
    {
        $this->level = $level;
    }

    /**
     * {@inheritdoc}
     */
    public function getAttributes(): ArrayCollection
    {
        return $this->attributes;
    }

    /**
     * {@inheritdoc}
     */
    public function setAttributes(ArrayCollection $attributes)
    {
        $this->attributes = $attributes;
    }

    /**
     * {@inheritdoc}
     */
    public function getAxes(): ArrayCollection
    {
        return $this->axes;
    }

    /**
     * {@inheritdoc}
     */
    public function setAxes(ArrayCollection $axes)
    {
        $this->axes = $axes;
    }
}
