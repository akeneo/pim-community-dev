<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2020 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\AssetManager\Infrastructure\Symfony\Command\MigrationPAM;

use Akeneo\AssetManager\Application\Attribute\EditAttribute\CommandFactory\EditAttributeCommand;
use Akeneo\AssetManager\Application\Attribute\EditAttribute\CommandFactory\EditOptionsCommand;
use Akeneo\AssetManager\Application\Attribute\EditAttribute\EditAttributeHandler;
use Akeneo\AssetManager\Domain\Model\AssetFamily\AssetFamilyIdentifier;
use Akeneo\AssetManager\Domain\Model\Attribute\AttributeCode;
use Akeneo\AssetManager\Domain\Model\Attribute\OptionCollectionAttribute;
use Akeneo\AssetManager\Domain\Repository\AttributeNotFoundException;
use Akeneo\AssetManager\Domain\Repository\AttributeRepositoryInterface;
use Doctrine\DBAL\Connection;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class MigrateAssetCategoryLabelsCommand extends Command
{
    protected static $defaultName = 'pimee:assets:migrate:migrate-asset-category-labels';

    private const DEFAULT_CATEGORIES_CODE = 'categories';

    /** @var AttributeRepositoryInterface */
    private $attributeRepository;

    /** @var EditAttributeHandler */
    private $editAttributeHandler;

    /** @var Connection */
    private $connection;

    public function __construct(
        AttributeRepositoryInterface $attributeRepository,
        EditAttributeHandler $editAttributeHandler,
        Connection $connection
    ) {
        parent::__construct($this::$defaultName);

        $this->attributeRepository = $attributeRepository;
        $this->editAttributeHandler = $editAttributeHandler;
        $this->connection = $connection;
    }

    protected function configure()
    {
        $this
            ->setHidden(true)
            ->setDescription('Copy the category labels into the Asset Manager categories field.')
            ->addArgument('asset-family-code', InputArgument::REQUIRED, 'The asset family code to migrate')
            ->addOption('categories-attribute-code', null, InputOption::VALUE_OPTIONAL, 'The code of the attribute containing categories in your asset family', self::DEFAULT_CATEGORIES_CODE)
        ;
    }

    public function execute(InputInterface $input, OutputInterface $output)
    {
        $io = new SymfonyStyle($input, $output);
        $familyCode = $input->getArgument('asset-family-code');
        $categoriesAttributeCode = $input->getOption('categories-attribute-code');

        $io->title(sprintf('Migration of the category labels of the asset family "%s"', $familyCode));

        try {
            $attribute = $this->attributeRepository->getByCodeAndAssetFamilyIdentifier(
                AttributeCode::fromString($categoriesAttributeCode),
                AssetFamilyIdentifier::fromString($familyCode)
            );
        } catch (AttributeNotFoundException $e) {
            $io->warning(sprintf('There is no attribute "%s" for the family "%s".', $categoriesAttributeCode, $familyCode));

            return -1;
        }

        if (!($attribute instanceof OptionCollectionAttribute)) {
            $io->writeln(sprintf('The field "%s" is not a multiple_option. No migration needed.', $categoriesAttributeCode));

            return 0;
        }

        $PAMCategoryCodes = [];
        foreach ($attribute->getAttributeOptions() as $attributeOption) {
            $PAMCategoryCodes[] = $attributeOption->getCode();
        }
        if (empty($PAMCategoryCodes)) {
            $io->writeln(sprintf('The field "%s" does not contain any option. No migration needed.', $categoriesAttributeCode));

            return 0;
        }

        $io->writeln(sprintf('%d category found.', count($PAMCategoryCodes)));

        $categoryLabels = $this->getPAMCategoryLabelsFromCategoryCodes($PAMCategoryCodes);
        if (empty($categoryLabels)) {
            $io->writeln('There is no translation for these PAM categories. No migration needed.');

            return 0;
        }

        $io->writeln(sprintf('%d translations found.', count($categoryLabels)));

        $options = $attribute->normalize()['options'];
        $count = 0;
        $skip = 0;
        foreach ($categoryLabels as $categoryLabel) {
            $categoryCode = $categoryLabel['category_code'];
            $label = $categoryLabel['label'];
            $locale = $categoryLabel['locale'];
            if (null === $label) {
                continue;
            }

            foreach ($options as $i => $currentOption) {
                if ($currentOption['code'] === $categoryCode) {
                    if (isset($options[$i]['labels'][$locale]) && $options[$i]['labels'][$locale] === $label) {
                        $skip++;
                    } else {
                        $options[$i]['labels'][$locale] = $label;
                        $count++;
                    }
                }
            }
        }

        $io->writeln(sprintf('Update the options of the field "%s" of the family "%s"...', $categoriesAttributeCode, $familyCode));
        $editAttributeCommand = new EditAttributeCommand(
            (string) $attribute->getIdentifier(), [
                new EditOptionsCommand($categoriesAttributeCode, $options)
            ]
        );

        ($this->editAttributeHandler)($editAttributeCommand);

        $io->success(sprintf('%d translations updated, %d translations skipped!', $count, $skip));

        return 0;
    }

    private function getPAMCategoryLabelsFromCategoryCodes(array $attributeOptionCodes): array
    {
        $query = <<<SQL
SELECT
  c.code AS category_code,
  t.label AS label,
  t.locale AS locale
FROM pimee_product_asset_category c
INNER JOIN pimee_product_asset_category_translation t ON c.id = t.foreign_key
WHERE code IN (:attributeOptionCodes)
SQL;

        return $this->connection->fetchAll(
            $query,
            ['attributeOptionCodes' => $attributeOptionCodes],
            ['attributeOptionCodes' => Connection::PARAM_STR_ARRAY]
        );
    }
}
