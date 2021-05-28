<?php

declare(strict_types=1);

namespace Akeneo\AssetManager\Infrastructure\Symfony\Command\MigrationPAM\ExportAssetsIntoCSVFiles;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * @author    Samir Boulil <samir.boulil@akeneo.com>
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 */
class ExportAssetsCommand extends Command
{
    private const ASSETS_CSV_FILENAME = 'assets.csv';
    private const VARIATIONS_CSV_FILENAME = 'variations.csv';

    protected static $defaultName = 'pimee:migrate-pam-assets:export-assets';

    /** * @var SymfonyStyle */
    private ?SymfonyStyle $io = null;

    /** * @var FindAssets */
    private FindAssets $findAssets;

    /** * @var FindVariations */
    private FindVariations $findVariations;

    public function __construct(FindAssets $findAssets, FindVariations $findVariations)
    {
        parent::__construct(static::$defaultName);
        $this->findAssets = $findAssets;
        $this->findVariations = $findVariations;
    }

    protected function configure()
    {
        $this
            ->setDescription('Export assets and their variations of a 3.x PIM into CSV')
            ->addArgument('fileDir', InputArgument::REQUIRED, 'The fileDir of the file to import.')
            ->setHidden(true)
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->io = new SymfonyStyle($input, $output);
        $fileDir = $input->getArgument('fileDir');
        $assetsFilePath = sprintf('%s/%s', $fileDir, self::ASSETS_CSV_FILENAME);
        $variationFilePath = sprintf('%s/%s', $fileDir, self::VARIATIONS_CSV_FILENAME);

        $this->io->title('Export PAM assets and their variations in CSV files from old PAM mysql tables..');
        $this->io->text([
            sprintf('This command will export the old PAM assets from mysql tables into 2 csv files in the "%s" directory:', $fileDir),
            sprintf('- "%s" : The assets and their information', $assetsFilePath),
            sprintf('- "%s" : The variations for each assets', $variationFilePath)
        ]);


        $hasValidTargetDir = $this->hasTargetDir($fileDir);
        if (!$hasValidTargetDir) {
            return 1;
        }
        $hasValidFilePaths = $this->hasValidFilePaths($assetsFilePath, $variationFilePath);
        if (!$hasValidFilePaths) {
            return 1;
        }

        $this->io->text('');

        $this->io->text('Now exporting the assets...');
        $numberOfAssets = $this->exportAssets($assetsFilePath);
        $this->io->text(sprintf('<info>%d assets have been exported</info>', $numberOfAssets));

        $this->io->text('');

        $this->io->text('Now exporting the asset variations...');
        $numberOfVariations= $this->exportVariations($variationFilePath);
        $this->io->text(sprintf('<info>%d asset variations have been exported</info>', $numberOfVariations));

        $this->io->text('');

        $this->io->text([
            '<info>All the assets and the variations have been successfully exported into:</info>',
            sprintf('- %s', $assetsFilePath),
            sprintf('- %s', $variationFilePath),
        ]);
    }

    private function hasValidFilePaths(string $assetsFilePath, string $variationFilePath): bool
    {
        $hasValidFilePath = true;
        if (file_exists($assetsFilePath)) {
            $this->io->warning(sprintf('The file "%s" already exists.', $assetsFilePath));
            $hasValidFilePath = false;
        }
        if (file_exists($variationFilePath)) {
            $this->io->warning(sprintf('The file "%s" already exists.', $variationFilePath));
            $hasValidFilePath = false;
        }

        return $hasValidFilePath;
    }

    private function hasTargetDir($fileDir): bool
    {
        if (!is_dir($fileDir)) {
            $this->io->warning(sprintf('The directory "%s" does not exist. Create it to run this command', $fileDir));
            return false;
        }

        return true;
    }

    private function exportAssets(string $assetsFilePath): int
    {
        $file = fopen($assetsFilePath, 'w');
        $numberOfAssets = 0;
        foreach ($this->findAssets->fetch() as $PAMAsset) {
            fputcsv($file, $PAMAsset, ';');
            $numberOfAssets++;
        }
        fclose($file);

        return $numberOfAssets;
    }

    private function exportVariations(string $variatonFilePath): int
    {
        $file = fopen($variatonFilePath, 'w');
        $numberOfVariations = 0;
        foreach ($this->findVariations->fetch() as $variations) {
            fputcsv($file, $variations, ';');
            $numberOfVariations++;
        }
        fclose($file);

        return $numberOfVariations;
    }
}
