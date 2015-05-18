<?php

namespace PimEnterprise\Bundle\ProductAssetBundle\Command;

use PimEnterprise\Component\ProductAsset\FileStorage\FileHandler\FileHandlerInterface;
use PimEnterprise\Component\ProductAsset\FileStorage\FileHandler\LocalFileHandler;
use PimEnterprise\Component\ProductAsset\FileStorage\ProductAssetFileSystems;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class TmpFakeUploadFileCommand extends TmpAbstractAssetCommand
{
    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setName('pim:product-asset:fake_upload')
            ->addArgument('file', InputArgument::REQUIRED);
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $filePath = $input->getArgument('file');
        if (!is_file($filePath)) {
            throw new \Exception(sprintf('"%s" is not a valid file path.', $filePath));
        }

        $file = new \SplFileInfo($filePath);
        $fileHandler = $this->getLocalFileHandler();
        $fileHandler->handle($file);
    }

    /**
     * @return FileHandlerInterface
     */
    private function getLocalFileHandler()
    {
        if (null === $this->localFileHandler) {
            $this->localFileHandler = new LocalFileHandler(
                $this->getPathGenerator(),
                $this->getMountManager(),
                $this->getFileSaver(),
                ProductAssetFileSystems::FS_PIM_TMP,
                ProductAssetFileSystems::FS_STORAGE
            );
        }

        return $this->localFileHandler;
    }
}
