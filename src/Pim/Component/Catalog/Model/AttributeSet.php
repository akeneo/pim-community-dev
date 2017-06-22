<?php

namespace Pim\Component\Catalog\Model;

use Doctrine\Common\Collections\ArrayCollection;

/**
 * @author    Arnaud Langlade <arnaud.langlade@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class AttributeSet implements AttributeSetInterface
{
    /** @var int */
    private $id;

    /** @var ArrayCollection */
    private $attributes;

    /** @var ArrayCollection */
    private $axes;

    public function __construct()
    {
        $this->attributes = new ArrayCollection();
        $this->axes = new ArrayCollection();
    }

    /**
     * {@inheritdoc}
     */
    public function getId(): int
    {
        return $this->id;
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
    public function setAttributes(array $attributes)
    {
        $this->attributes = new ArrayCollection($attributes);
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
    public function setAxes(array $axes)
    {
        $this->axes = new ArrayCollection($axes);
    }
}
