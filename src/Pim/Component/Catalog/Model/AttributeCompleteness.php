<?php

namespace Pim\Component\Catalog\Model;

/**
 * @author    Arnaud Langlade <arnaud.langlade@akeneo.com>
 * @copyright 2016 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class AttributeCompleteness
{
    /** @var CompletenessInterface */
    private $completeness;

    /** @var AttributeInterface */
    private $attribute;

    /** @var bool */
    private $isComplete;

    public function __construct(CompletenessInterface $completeness, AttributeInterface $attribute, $isComplete)
    {
        $this->completeness = $completeness;
        $this->attribute = $attribute;
        $this->isComplete = $isComplete;
    }

    public function isComplete()
    {
        return $this->isComplete;
    }
}
