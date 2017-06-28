<?php

namespace Pim\Component\Catalog\Model;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

/**
 * @author    Arnaud Langlade <arnaud.langlade@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class AttributeSet implements AttributeSetInterface
{
    /** @var int */
    private $id;

    /** @var Collection */
    private $attributes;

    /** @var Collection */
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
    public function getAttributes(): Collection
    {
        return $this->attributes;
    }

    /**
     * {@inheritdoc}
     */
    public function setAttributes(array $attributes): void
    {
        $this->attributes = new ArrayCollection($attributes);
    }

    /**
     * {@inheritdoc}
     */
    public function getAxes(): Collection
    {
        return $this->axes;
    }

    /**
     * {@inheritdoc}
     */
    public function setAxes(array $axes): void
    {
        $this->axes = new ArrayCollection($axes);
    }
}
