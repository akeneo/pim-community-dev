<?php

namespace Pim\Bundle\ImportExportBundle\Transformer\Guesser;

use Doctrine\ORM\Mapping\ClassMetadataInfo;
use Pim\Bundle\ImportExportBundle\Transformer\Guesser\GuesserInterface;
use Pim\Bundle\ImportExportBundle\Transformer\Property\PropertyTransformerInterface;
use Pim\Bundle\ImportExportBundle\Transformer\ColumnInfo\ColumnInfoInterface;

/**
 * Regexp guesser
 *
 * @author    Antoine Guigan <antoine@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class RegexpGuesser implements GuesserInterface
{
    /**
     * @var PropertyTransformerInterface
     */
    protected $transformer;

    /**
     * @var string
     */
    protected $class;

    /**
     * @var array
     */
    protected $regexps;

    /**
     * @var array
     */
    protected $options = array();

    /**
     * Constructor
     *
     * @param PropertyTransformerInterface $transformer
     * @param string                       $class
     * @param array                        $regexps
     * @param array                        $options
     */
    public function __construct(
        PropertyTransformerInterface $transformer,
        $class,
        array $regexps,
        array $options = array()
    ) {
        $this->transformer = $transformer;
        $this->class = $class;
        $this->regexps = $regexps;
        $this->options = $options;
    }

    /**
     * {@inheritdoc}
     */
    public function getTransformerInfo(ColumnInfoInterface $columnInfo, ClassMetadataInfo $metadata)
    {
        if ($this->class !== $metadata->getName()) {
            return;
        }

        foreach ($this->regexps as $regexp) {
            if (preg_match($regexp, $columnInfo->getLabel())) {
                return array($this->transformer, $this->options);
            }
        }

        return;
    }
}
