<?php
namespace Akeneo\Pim\Enrichment\Component\Product\Factory\Value;

use Akeneo\Pim\Enrichment\Component\Product\Model\ValueInterface;
use Akeneo\Pim\Structure\Component\Model\AttributeInterface;

/**
 * Factory that creates product values.
 *
 * @internal  Interface for the factories used internally by \Akeneo\Pim\Enrichment\Component\Product\Factory\ValueFactory.
 *
 * @author    Julien Janvier <j.janvier@gmail.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
interface ValueFactoryInterface
{
    /**
     * This method effectively creates a product value and directly set the data.
     * Channel and locale codes validity MUST HAVE BEEN checked BEFORE.
     *
     * @param AttributeInterface $attribute
     * @param string             $channelCode
     * @param string             $localeCode
     * @param mixed              $data
     *
     * @return ValueInterface
     *
     * @todo merge master : add an argument at the end  : "bool $ignoreUnknownData". Cf ReferenceDataCollectionValueFactory class.
     */
    public function create(AttributeInterface $attribute, $channelCode, $localeCode, $data);

    /**
     * @param string $attributeType
     *
     * @return bool
     */
    public function supports($attributeType);
}
