<?php

declare(strict_types=1);

namespace Akeneo\Pim\TableAttribute\Infrastructure\Connector\Tasklet;

use Akeneo\Channel\Infrastructure\Component\Query\PublicApi\GetChannelCodeWithLocaleCodesInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductModelInterface;
use Akeneo\Pim\Enrichment\Component\Product\Query\Filter\Operators;
use Akeneo\Pim\Enrichment\Component\Product\Query\ProductQueryBuilderFactoryInterface;
use Akeneo\Pim\Enrichment\Component\Product\Query\ProductQueryBuilderInterface;
use Akeneo\Pim\Structure\Component\AttributeTypes;
use Akeneo\Pim\Structure\Component\Query\PublicApi\AttributeType\Attribute;
use Akeneo\Pim\Structure\Component\Query\PublicApi\AttributeType\GetAttributes;
use Akeneo\Tool\Component\Batch\Model\StepExecution;
use Akeneo\Tool\Component\Connector\Step\TaskletInterface;
use Akeneo\Tool\Component\StorageUtils\Cursor\CursorInterface;
use Akeneo\Tool\Component\StorageUtils\Saver\BulkSaverInterface;
use Webmozart\Assert\Assert;

/**
 * @copyright 2021 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class CleanTableValuesWithDeletedOptionsTasklet implements TaskletInterface
{
    private const ALL_CHANNELS = '<all_channels>';
    private const ALL_LOCALES = '<all_locales>';

    private ProductQueryBuilderFactoryInterface $pqbFactory;
    private GetAttributes $getAttributes;
    private GetChannelCodeWithLocaleCodesInterface $getChannelCodeWithLocaleCodes;
    private BulkSaverInterface $productSaver;
    private BulkSaverInterface $productModelSaver;
    private ?StepExecution $stepExecution = null;
    private int $batchSize;
    private array $channelsAndLocales = [];

    public function __construct(
        ProductQueryBuilderFactoryInterface $pqbFactory,
        GetAttributes $getAttributes,
        GetChannelCodeWithLocaleCodesInterface $getChannelCodeWithLocaleCodes,
        BulkSaverInterface $productSaver,
        BulkSaverInterface $productModelSaver,
        int $batchSize
    ) {
        $this->pqbFactory = $pqbFactory;
        $this->getAttributes = $getAttributes;
        $this->getChannelCodeWithLocaleCodes = $getChannelCodeWithLocaleCodes;
        $this->productSaver = $productSaver;
        $this->productModelSaver = $productModelSaver;
        $this->batchSize = $batchSize;
    }

    public function execute(): void
    {
        Assert::notNull($this->stepExecution);
        $jobParameters = $this->stepExecution->getJobParameters();

        $attributeCode = $jobParameters->get('attribute_code');

        $attribute = $this->getAttributes->forCode($attributeCode);
        Assert::notNull($attribute);
        Assert::same($attribute->type(), AttributeTypes::TABLE);

        $removedOptionsByColumnCode = $jobParameters->get('removed_options_per_column_code');
        $channelsAndLocales = $this->getAttributeChannelsAndLocales($attribute);

        foreach (['root_pm', 'sub_pm', 'product'] as $entityType) {
            foreach ($removedOptionsByColumnCode as $columnCode => $removedOptions) {
                foreach ($channelsAndLocales as $channel => $locales) {
                    if (self::ALL_CHANNELS === $channel) {
                        $channel = null;
                    }
                    foreach ($locales as $locale) {
                        $this->cleanEntities(
                            $entityType,
                            $attributeCode,
                            $columnCode,
                            $removedOptions,
                            $locale === self::ALL_LOCALES ? null : $locale,
                            $channel
                        );
                    }
                }
            }
        }
    }

    /**
     * {@inheritDoc}
     */
    public function setStepExecution(StepExecution $stepExecution): void
    {
        $this->stepExecution = $stepExecution;
    }

    private function cleanEntities(
        string $entityType,
        string $attributeCode,
        string $columnCode,
        array $removedOptionCodes,
        ?string $locale,
        ?string $channel
    ): void {
        $pqb = $this->getProductQueryBuilder($entityType);
        $pqb->addFilter(
            $attributeCode,
            Operators::IN_LIST,
            ['column' => $columnCode, 'value' => $removedOptionCodes],
            ['locale' => $locale, 'scope' => $channel]
        );
        $this->saveEntities($pqb->execute(), 'product' !== $entityType);
    }

    private function getProductQueryBuilder(string $entityType): ProductQueryBuilderInterface
    {
        switch ($entityType) {
            case 'root_pm':
                return $this->pqbFactory->create([
                    'filters' => [
                        [
                            'field' => 'entity_type',
                            'operator' => Operators::EQUALS,
                            'value' => ProductModelInterface::class,
                        ],
                        ['field' => 'parent', 'operator' => Operators::IS_EMPTY, 'value' => null],
                    ],
                ]);
            case 'sub_pm':
                return $this->pqbFactory->create([
                    'filters' => [
                        [
                            'field' => 'entity_type',
                            'operator' => Operators::EQUALS,
                            'value' => ProductModelInterface::class,
                        ],
                        ['field' => 'parent', 'operator' => Operators::IS_NOT_EMPTY, 'value' => null],
                    ],
                ]);
            case 'product':
                return $this->pqbFactory->create([
                    'filters' => [
                        [
                            'field' => 'entity_type',
                            'operator' => Operators::EQUALS,
                            'value' => ProductInterface::class,
                        ],
                    ],
                ]);
        }

        throw new \InvalidArgumentException();
    }

    private function saveEntities(CursorInterface $productOrModels, bool $saveProductModels): void
    {
        $saver = $saveProductModels ? $this->productModelSaver : $this->productSaver;
        $batch = [];
        foreach ($productOrModels as $productOrModel) {
            $batch[] = $productOrModel;
            if (\count($batch) >= $this->batchSize) {
                $saver->saveAll($batch, ['force_save' => true]);
                $this->stepExecution->incrementProcessedItems(\count($batch));
                $batch = [];
            }
        }

        if (\count($batch) > 0) {
            $saver->saveAll($batch, ['force_save' => true]);
            $this->stepExecution->incrementProcessedItems(\count($batch));
        }
    }

    private function getAttributeChannelsAndLocales(Attribute $attribute): array
    {
        if ($attribute->isScopable() || $attribute->isLocalizable()) {
            $channelsAndLocales = $this->getAllLocalesIndexedByChannel();

            if ($attribute->isScopable() && $attribute->isLocalizable()) {
                return $channelsAndLocales;
            } elseif ($attribute->isScopable()) {
                return \array_fill_keys(\array_keys($channelsAndLocales), [self::ALL_LOCALES]);
            } elseif ($attribute->isLocalizable()) {
                return [self::ALL_CHANNELS => \array_values(\array_unique(\array_merge(\array_values($channelsAndLocales))))];
            }
        }

        return [
            self::ALL_CHANNELS => [self::ALL_LOCALES],
        ];
    }

    private function getAllLocalesIndexedByChannel(): array
    {
        if ([] === $this->channelsAndLocales) {
            foreach ($this->getChannelCodeWithLocaleCodes->findAll() as $item) {
                $this->channelsAndLocales[$item['channelCode']] = $item['localeCodes'];
            }
        }

        return $this->channelsAndLocales;
    }
}
