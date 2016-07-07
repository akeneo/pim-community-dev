<?php

namespace Akeneo\Component\Batch\Item;

/**
 * File Invalid Item
 *
 * @author    Olivier Soulet <olivier.soulet@akeneo.com>
 * @copyright 2016 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class FileInvalidItem implements InvalidItemInterface
{
    /** @var array */
    protected $invalidData;

    /** @var int */
    protected $lineNumber;

    /**
     * @param array $invalidData
     */
    public function __construct(array $invalidData)
    {
        $this->invalidData = $invalidData;
    }

    /**
     * {@inheritdoc}
     */
    public function getData()
    {
        return $this->invalidData;
    }

    /**
     * @return int
     */
    public function getLineNumber()
    {
        return $this->lineNumber;
    }
}
