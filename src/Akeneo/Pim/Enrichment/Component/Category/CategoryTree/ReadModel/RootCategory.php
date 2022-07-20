<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Component\Category\CategoryTree\ReadModel;

/**
 * DTO representing the root categories in the category tree.
 *
 * @author    Alexandre Hocquard <alexandre.hocquard@akeneo.com>
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class RootCategory
{
    /** @var integer */
    private $id;

    /** @var string */
    private $code;

    /** @var string */
    private $label;

    /** @var int */
    private $numberProductsInCategory;

    /** @var bool */
    private $selected;

    /**
     * @param int    $id
     * @param string $code
     * @param string $label
     * @param int    $numberProductsInCategory
     * @param bool   $selected
     */
    public function __construct(
        int $id,
        string $code,
        string $label,
        int $numberProductsInCategory,
        bool $selected
    ) {
        $this->id = $id;
        $this->code = $code;
        $this->label = $label;
        $this->numberProductsInCategory = $numberProductsInCategory;
        $this->selected = $selected;
    }

    /**
     * @return int
     */
    public function id(): int
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function code(): string
    {
        return $this->code;
    }

    /**
     * @return string
     */
    public function label(): string
    {
        return $this->label;
    }

    /**
     * @return int
     */
    public function numberProductsInCategory(): int
    {
        return $this->numberProductsInCategory;
    }

    /**
     * @return bool
     */
    public function selected(): bool
    {
        return $this->selected;
    }
}
