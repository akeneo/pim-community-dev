<?php
declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Component\Product\Factory\Value;

use Akeneo\Pim\Enrichment\Component\Product\Model\ValueInterface;
use Akeneo\Pim\Structure\Component\Query\PublicApi\AttributeType\Attribute;
use Akeneo\Tool\Component\StorageUtils\Exception\PropertyException;

/**
 * @author    Anael Chardan <anael.chardan@akeneo.com>
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
interface ValueFactory
{
    /**
     * Create the data by validating the data type.
     * For example, it checks that an expected scalar is a indeed scalar.
     *
     * This is done to guarantee that data coming in to the domain are good.
     * This validation has a non-negligeable cost though.
     */
    public function createByCheckingData(Attribute $attribute, ?string $channelCode, ?string $localeCode, $data): ValueInterface;

    /**
     * Create the data without validating the data type.
     * For example, it will NOT check that the data for a text attribute is a scalar.
     *
     * It has been designed for performance purpose, as doing check on type when reading from database should not be done.
     *
     * @throws PropertyException
     */
    public function createWithoutCheckingData(Attribute $attribute, ?string $channelCode, ?string $localeCode, $data): ValueInterface;

    public function supportedAttributeType(): string;
}
