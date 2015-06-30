<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2015 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PimEnterprise\Bundle\ProductAssetBundle\TwigExtension;

/**
 * Verify if file exists in web/
 *
 * @author Willy Mesnage <willy.mesnage@akeneo.com>
 */
class RawFileExistsTwigExtension extends \Twig_Extension
{
    /** @var string */
    protected $rootFolder;

    /**
     * @param string $rootFolder
     */
    public function __construct($rootFolder)
    {
        $this->rootFolder = $rootFolder;
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
        $webRoot = realpath($this->rootFolder);
        $toCheck = realpath($webRoot . $path);

        if (false === $webRoot || false === $toCheck) {
            return false;
        }

        if (!is_file($toCheck)) {
            return false;
        }

        if (strpos($toCheck, $webRoot) === false) {
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
