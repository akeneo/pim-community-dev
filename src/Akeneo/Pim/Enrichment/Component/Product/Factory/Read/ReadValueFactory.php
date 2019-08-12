<?php
declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Component\Product\Factory\Read;

use Akeneo\Pim\Enrichment\Component\Product\Factory\Read\Value\ReadValueFactory as ValueFactory;
use Akeneo\Pim\Enrichment\Component\Product\Factory\ValueFactory as WriteValueFactory;
use Akeneo\Pim\Enrichment\Component\Product\Model\ValueInterface;
use Akeneo\Pim\Structure\Component\Query\PublicApi\AttributeType\Attribute;
use Akeneo\Tool\Component\StorageUtils\Repository\IdentifiableObjectRepositoryInterface;
use Webmozart\Assert\Assert;

/**
 * This value factory bypasses the validation done in the write value factory.
 * The validation when reading data from the database is useless.
 * It is done for performance reason, as validation is costly.
 *
 * @author    Anael Chardan <anael.chardan@akeneo.com>
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class ReadValueFactory
{
    /** @var array|ValueFactory[] */
    private $readValueFactories;

    /** @var WriteValueFactory */
    private $writeValueFactory;

    /** @var IdentifiableObjectRepositoryInterface */
    private $attributeRepository;

    public function __construct(iterable $readValueFactories, WriteValueFactory $writeValueFactory, IdentifiableObjectRepositoryInterface $attributeRepository)
    {
        Assert::allIsInstanceOf($readValueFactories, ValueFactory::class);

        /** @var ReadValueFactory $readValueFactory */
        foreach ($readValueFactories as $readValueFactory) {
            $this->readValueFactories[$readValueFactory->supportedAttributeType()] = $readValueFactory;
        }

        $this->writeValueFactory = $writeValueFactory;
        $this->attributeRepository = $attributeRepository;
    }

    public function create(Attribute $attribute, ?string $channelCode, ?string $localeCode, $data): ValueInterface
    {
        if (isset($this->readValueFactories[$attribute->type()])) {
            return $this->readValueFactories[$attribute->type()]->create($attribute, $channelCode, $localeCode, $data);
        }

        return $this->fallbackToWriteModelValueFactory($attribute->code(), $channelCode, $localeCode, $data);
    }

    /**
     * This fallback is useful if a new attribute type is added but the read value factory is missing.
     * In that case, it fallbacks on the write value factory.
     */
    private function fallbackToWriteModelValueFactory(string $attributeCode, ?string $channelCode, ?string $localeCode, $data): ValueInterface
    {
        $attributeFromWriteModel = $this->attributeRepository->findOneByIdentifier($attributeCode);

        return $this->writeValueFactory->create($attributeFromWriteModel, $channelCode, $localeCode, $data);
    }
}
