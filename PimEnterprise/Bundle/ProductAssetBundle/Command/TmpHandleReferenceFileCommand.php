<?php

namespace PimEnterprise\Bundle\ProductAssetBundle\Command;

use DamEnterprise\Component\Transformer\TransformerInterface;
use League\Flysystem\Adapter\Local;
use League\Flysystem\Filesystem;
use League\Flysystem\MountManager;
use Metadata\MetadataFactory;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class TmpHandleReferenceFileCommand extends TmpAbstractAssetCommand
{
    const TEST_FILE = 'akene.jpg';


    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this->setName('pim:product-asset:reference');
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        //download file locally
        $this->downloadFile();

        //extract metadata


        //build thumbnails
        //move the thumbnail
        //generate variations

    }
}
