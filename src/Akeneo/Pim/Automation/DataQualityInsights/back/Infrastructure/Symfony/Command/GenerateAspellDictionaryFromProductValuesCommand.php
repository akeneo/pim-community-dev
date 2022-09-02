<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2019 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Pim\Automation\DataQualityInsights\Infrastructure\Symfony\Command;

use Akeneo\Pim\Automation\DataQualityInsights\Domain\Filter\LocaleCodeByLanguageCodeFilterIterator;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Model\LocaleCollection;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Query\Structure\GetAllActivatedLocalesQueryInterface;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\LanguageCode;
use Akeneo\Pim\Automation\DataQualityInsights\Infrastructure\Spellcheck\AspellDictionary;
use Akeneo\Pim\Automation\DataQualityInsights\Infrastructure\Spellcheck\AspellDictionaryGenerator;
use Akeneo\Pim\Automation\DataQualityInsights\Infrastructure\Spellcheck\ProductValueInDatabaseDictionarySource;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class GenerateAspellDictionaryFromProductValuesCommand extends Command
{
    protected static $defaultName = 'pimee:data-quality-insights:generate-aspell-dictionary-from-product-values';
    protected static $defaultDescription = 'Extract most present words in the product values to create a spelling dictionary';

    private ProductValueInDatabaseDictionarySource $productValueInDatabaseDictionarySource;
    private AspellDictionary $aspellDictionary;
    private AspellDictionaryGenerator $aspellDictionaryGenerator;
    private GetAllActivatedLocalesQueryInterface $allActivatedLocalesQuery;

    public function __construct(
        ProductValueInDatabaseDictionarySource $productValueInDatabaseDictionarySource,
        AspellDictionary $aspellDictionary,
        AspellDictionaryGenerator $aspellDictionaryGenerator,
        GetAllActivatedLocalesQueryInterface $allActivatedLocalesQuery
    ) {
        parent::__construct();

        $this->productValueInDatabaseDictionarySource = $productValueInDatabaseDictionarySource;
        $this->aspellDictionary = $aspellDictionary;
        $this->aspellDictionaryGenerator = $aspellDictionaryGenerator;
        $this->allActivatedLocalesQuery = $allActivatedLocalesQuery;
    }

    protected function configure()
    {
        $this
            ->addOption(
                'language-codes',
                'l',
                InputOption::VALUE_OPTIONAL | InputOption::VALUE_IS_ARRAY,
                'Language codes'
            )
            ->setHidden(true);
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        if (!empty($input->getOption('language-codes'))) {
            $this->customLanguageCodes($input, $output);

            return Command::SUCCESS;
        }

        $this->aspellDictionaryGenerator
            ->ignoreCheckTimestamp()
            ->generate($this->productValueInDatabaseDictionarySource);

        $output->writeln('<info>Dictionaries generated and pushed to shared filesystem.</info>');

        return Command::SUCCESS;
    }

    private function customLanguageCodes(InputInterface $input, OutputInterface $output)
    {
        $languageCodes = $input->getOption('language-codes');

        $allActivatedLocales = $this->allActivatedLocalesQuery->execute();

        foreach ($languageCodes as $languageCode) {
            $languageCode = new LanguageCode($languageCode);

            $localesCode = iterator_to_array(new LocaleCodeByLanguageCodeFilterIterator($allActivatedLocales->getIterator(), $languageCode));

            if (count($localesCode) === 0) {
                $output->writeln(sprintf('<comment>Nothing to generate for %s language code</comment>', $languageCode->__toString()));

                continue;
            }

            $dictionary = $this->productValueInDatabaseDictionarySource->getDictionary(new LocaleCollection($localesCode));

            if (count($dictionary) === 0) {
                $output->writeln(sprintf('<comment>Nothing to generate for %s language code</comment>', $languageCode->__toString()));

                continue;
            }

            $this->aspellDictionary->persistDictionaryToSharedFilesystem($dictionary, $languageCode);

            $output->writeln(sprintf('<info>Dictionary generated and pushed to shared filesystem for <comment>%s</comment> language code</info>', $languageCode->__toString()));
        }
    }
}
