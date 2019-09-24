<?php

/**
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2015 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Akeneo\Asset\Bundle\Command;

use Akeneo\Asset\Component\ProcessedItem;
use Akeneo\Asset\Component\Upload\MassUpload\MassUploadProcessor;
use Akeneo\Asset\Component\Upload\UploadContext;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Process uploaded assets files
 *
 * @author JM Leroux <jean-marie.leroux@akeneo.com>
 */
class ProcessMassUploadCommand extends Command
{
    const NAME = 'pim:product-asset:mass-upload';

    protected static $defaultName = self::NAME;

    /** @var MassUploadProcessor */
    private $massUploadProcessor;

    /** @var string */
    private $tmpStorageDir;

    public function __construct(
        MassUploadProcessor $massUploadProcessor,
        string $tmpStorageDir
    ) {
        parent::__construct();

        $this->tmpStorageDir = $tmpStorageDir;
        $this->massUploadProcessor = $massUploadProcessor;
    }

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->addOption(
                'user',
                null,
                InputOption::VALUE_REQUIRED,
                'Username to process'
            );
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $sourceDir = $input->getOption('user');
        $context = new UploadContext($this->tmpStorageDir, $sourceDir);
        $processedList = $this->massUploadProcessor->applyMassUpload($context);

        foreach ($processedList as $item) {
            $file = $item->getItem();

            switch ($item->getState()) {
                case ProcessedItem::STATE_ERROR:
                    $msg = sprintf("<error>%s\n%s</error>", $file->getFilename(), $item->getReason());
                    break;
                case ProcessedItem::STATE_SKIPPED:
                    $msg = sprintf('%s <comment>Skipped (%s)</comment>', $file->getFilename(), $item->getReason());
                    break;
                default:
                    $msg = sprintf('%s <info>processed</info>', $file->getFilename());
                    break;
            }

            $output->writeln($msg);
        }

        $output->writeln('<info>Done !</info>');

        return 0;
    }
}
