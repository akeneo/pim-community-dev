<?php

namespace Akeneo\Platform\Bundle\InstallerBundle\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Dump a file called require-paths containing a list of required bundle paths
 *
 * @author Tamara Robichet <tamara.robichet@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class DumpRequirePathsCommand extends Command
{
    protected static $defaultName = 'pim:installer:dump-require-paths';

    const MAIN_CONFIG_FILE_NAME = 'js/require-paths.js';

    /** @var string */
    private $rootDir;

    /** @var array */
    private $bundles;

    public function __construct(
        string $rootDir,
        array $bundles
    ) {
        parent::__construct();

        $this->rootDir = $rootDir;
        $this->bundles = $bundles;
    }

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this->setDescription('Dump the paths for all the requirejs.yml files for each bundle');
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $output->writeln('Generating require.js main config');
        $webRoot = realpath($this->rootDir . '/../public');

        $mainConfigContent = json_encode($this->collectConfigPaths($this->rootDir), JSON_UNESCAPED_SLASHES);
        $mainConfigContent = 'module.exports = ' . $mainConfigContent;
        $mainConfigContent = str_replace(',', ",\n", $mainConfigContent);
        $mainConfigFilePath = $webRoot . DIRECTORY_SEPARATOR . self::MAIN_CONFIG_FILE_NAME;
        if (false === file_put_contents($mainConfigFilePath, $mainConfigContent)) {
            throw new \RuntimeException('Unable to write file ' . $mainConfigFilePath);
        }
    }

    /**
     * Collect an array of requirejs.yml paths for each bundle
     * @param string $rootDir
     * @return array
     */
    protected function collectConfigPaths(string $rootDir)
    {
        $paths = array();
        $rootDir = realpath($rootDir . '/../') . '/';

        foreach ($this->bundles as $bundle) {
            $reflection = new \ReflectionClass($bundle);
            $fileName = dirname($reflection->getFilename());
            $relativeFileName = substr($fileName, strlen($rootDir));
            $paths[] = $relativeFileName;
        }

        return $paths;
    }
}
