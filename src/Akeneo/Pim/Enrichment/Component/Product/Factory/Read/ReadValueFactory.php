<?php
declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Component\Product\Factory\Read;

use Akeneo\Pim\Enrichment\Component\Product\Factory\Read\Value\ReadValueFactory as ValueFactory;
use Akeneo\Pim\Enrichment\Component\Product\Model\ValueInterface;
use Akeneo\Pim\Structure\Component\Query\PublicApi\AttributeType\Attribute;
use Webmozart\Assert\Assert;

/**
 * @author    Anael Chardan <anael.chardan@akeneo.com>
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class ReadValueFactory
{
    /** @var array|ValueFactory[] */
    private $valueFactories;

    public function __construct(iterable $valueFactories)
    {
        Assert::allIsInstanceOf($valueFactories, ValueFactory::class);

        /** @var ReadValueFactory $readValueFactory */
        foreach ($valueFactories as $readValueFactory) {
            $this->valueFactories[$readValueFactory->supportedAttributeType()] = $readValueFactory;
        }
    }

    public function createWithoutCheckingData(Attribute $attribute, ?string $channelCode, ?string $localeCode, $data): ValueInterface
    {
        return $this->getFactory($attribute)->createWithoutCheckingData($attribute, $channelCode, $localeCode, $data);
    }

    public function createByCheckingData(Attribute $attribute, ?string $channelCode, ?string $localeCode, $data): ValueInterface
    {
        return $this->getFactory($attribute)->createByCheckingData($attribute, $channelCode, $localeCode, $data);
    }

    private function getFactory(Attribute $attribute): ValueFactory
    {
        if (isset($this->valueFactories[$attribute->type()])) {
            return $this->valueFactories[$attribute->type()];
        }

        throw new \OutOfBoundsException(sprintf(
            'No factory has been registered to create a Product Value for the attribute type "%s"',
            $attribute->type()
        ));
    }
}
