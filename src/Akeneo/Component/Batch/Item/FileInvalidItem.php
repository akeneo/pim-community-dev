<?php

namespace Akeneo\Component\Batch\Item;

/**
 * This class handles invalid items that could be raised by Reader or Processor. This invalid item class will handle
 * file invalid items (for example items coming from a csv file)
 *
 * @author    Olivier Soulet <olivier.soulet@akeneo.com>
 * @copyright 2016 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 * @api
 */
class FileInvalidItem implements InvalidItemInterface
{
    /** @var array */
    protected $invalidData;

    /** @var int */
    protected $lineNumber;

    /**
     * @param array $invalidData
     * @param int   $lineNumber
     */
    public function __construct(array $invalidData, $lineNumber)
    {
        $this->invalidData = $invalidData;
        $this->lineNumber = $lineNumber;
    }

    /**
     * {@inheritdoc}
     */
    public function getInvalidData()
    {
        return $this->invalidData;
    }

    /**
     * @return int
     *
     * @api
     */
    public function getLineNumber()
    {
        return $this->lineNumber;
    }
}
