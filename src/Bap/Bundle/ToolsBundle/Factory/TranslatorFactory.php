<?php
namespace Bap\Bundle\ToolsBundle\Factory;

use Symfony\Component\Translation\Dumper\PhpFileDumper;
use Symfony\Component\Translation\Dumper\XliffFileDumper;
use Symfony\Component\Translation\Dumper\YamlFileDumper;

use Symfony\Component\Translation\Loader\PhpFileLoader;
use Symfony\Component\Translation\Loader\XliffFileLoader;
use Symfony\Component\Translation\Loader\YamlFileLoader;

/**
 * Translator factory
 * Allow to return loader or dumper according to the requested format
 *
 * @author    Romain Monceau <romain@akeneo.com>
 * @copyright 2012 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 */
class TranslatorFactory
{
    /**
     * Factory to get loader according to the format
     * @param string $format
     *
     * @return \Symfony\Component\Translation\Loader\LoaderInterface
     * @static
     * @throws \Exception
     */
    public static function createLoader($format)
    {
        switch ($format) {
            case 'yml':
            case 'yaml':
                return new YamlFileLoader();
            case 'xliff':
                return new XliffFileLoader();
            case 'php':
                return new PhpFileLoader();
            default:
                throw new \Exception('not yet implemented');
        }
    }

    /**
     * Factory to get dumper according to the format
     * @param string $format
     *
     * @return \Symfony\Component\Translation\Dumper\DumperInterface
     * @static
     * @throws \Exception
     */
    public static function createDumper($format)
    {
        switch ($format) {
            case 'yml':
            case 'yaml':
                return new YamlFileDumper();
            case 'xliff':
                return XliffFileDumper();
            case 'php':
                return PhpFileDumper();
            default:
                throw new \Exception('not yet implemented');
        }
    }
}