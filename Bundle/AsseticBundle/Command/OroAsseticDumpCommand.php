<?php

namespace Oro\Bundle\AsseticBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Assetic\Asset\AssetInterface;
use Assetic\Util\VarUtils;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\HttpFoundation\Request;

use Oro\Bundle\AsseticBundle\Factory\OroAssetManager;

class OroAsseticDumpCommand extends ContainerAwareCommand
{
    /**
     * @var OroAssetManager
     */
    protected $am;

    protected function configure()
    {
        $this
            ->setName('oro:assetic:dump')
            ->setDescription('Dumps oro assetics')
            ->addArgument('write_to', InputArgument::OPTIONAL, 'Override the configured asset root')
        ;
    }

    protected function initialize(InputInterface $input, OutputInterface $output)
    {
        $this->basePath = $input->getArgument('write_to') ?: $this->getContainer()->getParameter('assetic.write_to');
        $this->am = $this->getContainer()->get('oro_assetic.asset_manager');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $output->writeln(sprintf('Dumping all <comment>%s</comment> assets.', $input->getOption('env')));
        $output->writeln(sprintf('Debug mode is <comment>%s</comment>.', 'off'));
        $output->writeln('');

        //$this->dumpCss($output);
        $this->dumpJs($output);
    }

    protected function dumpCss($output)
    {
        $filters = array(
            'cssrewrite',
            'lessphp',
            'yui_css'
        );
        $assetCollection = $this->af->createAsset($this->cssFiles, $filters);
        $assetCollection->setTargetPath("css/oro.bootstrap.css");
        $this->doDump($assetCollection, $output);
    }

    protected function dumpJs($output)
    {
        foreach ($this->am->getAssets() as $asset) {
            /** @var  $asset \Oro\Bundle\AsseticBundle\Node\OroAsseticNode */
            $this->doDump($asset->getCompressAsset(), $output);
        }
    }

    /**
     * Performs the asset dump.
     *
     * @param AssetInterface  $asset  An asset
     * @param OutputInterface $output The command output
     *
     * @throws RuntimeException If there is a problem writing the asset
     */
    private function doDump(AssetInterface $asset, OutputInterface $output)
    {

        foreach ($this->getAssetVarCombinations($asset) as $combination) {
            $asset->setValues($combination);

            // resolve the target path
            $target = rtrim($this->basePath, '/').'/'.$asset->getTargetPath();
            $target = str_replace('_controller/', '', $target);
            $target = VarUtils::resolve($target, $asset->getVars(), $asset->getValues());

            if (!is_dir($dir = dirname($target))) {
                $output->writeln(sprintf(
                    '<comment>%s</comment> <info>[dir+]</info> %s',
                    date('H:i:s'),
                    $dir
                ));

                if (false === @mkdir($dir, 0777, true)) {
                    throw new \RuntimeException('Unable to create directory '.$dir);
                }
            }

            $output->writeln(sprintf(
                '<comment>%s</comment> <info>[file+]</info> %s',
                date('H:i:s'),
                $target
            ));

            if (false === @file_put_contents($target, $asset->dump())) {
                throw new \RuntimeException('Unable to write file '.$target);
            }
        }
    }

    private function getAssetVarCombinations(AssetInterface $asset)
    {
        return VarUtils::getCombinations(
            $asset->getVars(),
            $this->getContainer()->getParameter('assetic.variables')
        );
    }
}