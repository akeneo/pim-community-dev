<?php

/**
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2015 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PimEnterprise\Bundle\ProductAssetBundle\TwigExtension;

use Symfony\Component\HttpKernel\KernelInterface;

/**
 * Verify if file exists in web/
 *
 * @author Willy Mesnage <willy.mesnage@akeneo.com>
 */
class RawFileExistsTwigExtension extends \Twig_Extension
{
    /** @var string */
    protected $assetFolder;

    /**
     * @param string $assetFolder
     */
    public function __construct($assetFolder)
    {
        $this->assetFolder = $assetFolder;
    }

    /**
     * Return functions registered as twig extensions
     *
     * @return array
     */
    public function getFunctions()
    {
        return [
            'fileExists' => new \Twig_Function_Method($this, 'fileExists'),
        ];
    }

    /**
     * Checks if the file exists in web/ folder
     *
     * @param string $path
     *
     * @return bool
     */
    public function fileExists($path)
    {
        $webRoot = realpath($this->assetFolder);
        $toCheck = realpath($webRoot . $path);

        if (!is_file($toCheck)) {
            return false;
        }

        // check if file is well contained in web/ directory (prevents ../ in paths)
        if (strncmp($webRoot, $toCheck, strlen($webRoot)) !== 0) {
            return false;
        }

        return true;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'twig_extension';
    }
}
