<?php

namespace Pim\Bundle\TransformBundle\Transformer\Guesser;

use Doctrine\Common\Persistence\Mapping\ClassMetadata;
use Pim\Bundle\TransformBundle\Transformer\ColumnInfo\ColumnInfoInterface;
use Pim\Bundle\TransformBundle\Transformer\Property\PropertyTransformerInterface;

/**
 * Translation guesser
 *
 * @author    Antoine Guigan <antoine@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class TranslationGuesser implements GuesserInterface
{
    /**
     * @var PropertyTransformerInterface
     */
    protected $transformer;

    /**
     * Constructor
     *
     * @param PropertyTransformerInterface $transformer
     */
    public function __construct(PropertyTransformerInterface $transformer)
    {
        $this->transformer = $transformer;
    }

    /**
     * {@inheritdoc}
     */
    public function getTransformerInfo(ColumnInfoInterface $columnInfo, ClassMetadata $metadata)
    {
        if ((!$columnInfo->getLocale() && !count($columnInfo->getSuffixes())) ||
            !$metadata->hasAssociation('translations')
        ) {
            return;
        }

        return array($this->transformer, array());
    }
}
