<?php

namespace Pim\Bundle\ImportExportBundle\Transformer\Guesser;

use Doctrine\ORM\Mapping\ClassMetadataInfo;
use Hermes\Bundle\ImportExportBundle\Transformer\Guesser\GuesserInterface;
use Pim\Bundle\ImportExportBundle\Transformer\Property\PropertyTransformerInterface;

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
    protected $skipRegexps;

    /**
     * Constructor
     * 
     * @param PropertyTransformerInterface $transformer
     * @param string $class
     * @param array $skipRegexps
     */
    function __construct(PropertyTransformerInterface $transformer, $class, array $skipRegexps)
    {
        $this->transformer = $transformer;
        $this->class = $class;
        $this->skipRegexps = $skipRegexps;
    }

    /**
     * {@inheritdoc}
     */
    public function getTransformerInfo(array $columnInfo, ClassMetadataInfo $metadata)
    {
        if ($this->class !== $metadata->getName()) {
            return;
        }

        foreach($this->skipRegexps as $regexp) {
            if (preg_match($regexp, $columnInfo['label'])) {
                return;
            }
        }

        return array($this->transformer, array());
    }
}