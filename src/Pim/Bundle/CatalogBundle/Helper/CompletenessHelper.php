<?php

namespace Pim\Bundle\CatalogBundle\Helper;

use Symfony\Component\Validator\ValidatorInterface;
use Pim\Bundle\CatalogBundle\Model\ProductInterface;
use Pim\Bundle\CatalogBundle\Entity\Locale;
use Pim\Bundle\CatalogBundle\Entity\Channel;
use Pim\Bundle\CatalogBundle\Validator\Constraints\ProductValueNotBlank;

/**
 * Completeness helper to get missing attributes
 *
 * @author    Gildas Quemener <gildas@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl3.0.php  Open Software License (OSL 3.0)
 */
class CompletenessHelper
{
    /** @var ValidatorInterface */
    protected $validator;

    /**
     * Constructor
     *
     * @param ValidatorInterface $validator
     */
    public function __construct(ValidatorInterface $validator)
    {
        $this->validator = $validator;
    }

    /**
     * Get blank attributes of the product that are required by its family
     *
     * @param ProductInterface $product
     * @param Locale           $locale
     * @param Channel          $channel
     *
     * @return \Pim\Bundle\CatalogBundle\Entity\ProductAttribute[]
     */
    public function getMissingAttributes(ProductInterface $product, Locale $locale, Channel $channel)
    {
        $missingAttributes = array();
        if ($family = $product->getFamily()) {
            foreach ($family->getAttributeRequirements() as $requirement) {
                if ($channel === $requirement->getChannel() && $requirement->isRequired()) {
                    $attribute  = $requirement->getAttribute();
                    $value      = $product->getValue($attribute->getCode(), $locale->getCode(), $channel->getCode());
                    $constraint = new ProductValueNotBlank(array('channel' => $channel));

                    if ($this->validator->validateValue($value, $constraint)->count()) {
                        $missingAttributes[] = $attribute;
                    }
                }
            }
        }

        return $missingAttributes;
    }
}
