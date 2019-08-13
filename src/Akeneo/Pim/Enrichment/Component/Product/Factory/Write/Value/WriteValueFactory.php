<?php
declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Component\Product\Factory\Read\Value;

use Akeneo\Pim\Enrichment\Component\Product\Model\ValueInterface;
use Akeneo\Pim\Structure\Component\Query\PublicApi\AttributeType\Attribute;

/**
 * The goal of this factory is create values by doing check on the validity of the data,
 * such as validation of a date is effectively a date or a scalar is really a scalar.
 *
 * This kind of check is useful to guarantee the data in your domain when creating a value from the outside world (API, UI, etc).
 *
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
interface WriteValueFactory
{
    public function create(Attribute $attribute, ?string $channelCode, ?string $localeCode, $data): ValueInterface;
    public function supportedAttributeType(): string;
}
