<?php

namespace Akeneo\Pim\Enrichment\Component\Product\Model;

/**
 * Reference data abstract class
 *
 * @author    Julien Janvier <jjanvier@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
abstract class AbstractReferenceData implements ReferenceDataInterface
{
    /** @var mixed */
    protected $id;

    /** @var string */
    protected $code;

    /** @var int */
    protected $sortOrder = 1;

    /**
     * {@inheritdoc}
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * {@inheritdoc}
     */
    public function getCode(): string
    {
        return $this->code;
    }

    /**
     * {@inheritdoc}
     */
    public function setCode(string $code): ReferenceDataInterface
    {
        $this->code = $code;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getSortOrder(): int
    {
        return $this->sortOrder;
    }

    public function setSortOrder(int $sortOrder): self
    {
        $this->sortOrder = $sortOrder;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public static function getLabelProperty(): ?string
    {
        return null;
    }

    /**
     * {@inheritdoc}
     */
    public function __toString()
    {
        if (null !== $labelProperty = static::getLabelProperty()) {
            $getter = 'get' . ucfirst($labelProperty);
            $label = $this->$getter();

            if (!empty($label)) {
                return $label;
            }
        }

        return sprintf('[%s]', $this->code);
    }
}
