<?php

namespace Pim\Component\Connector\Writer\File;

/**
 * File path resolvers must only implement the resolve() method.
 *
 * @author    Yohan Blain <yohan.blain@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
interface FilePathResolverInterface
{
    /**
     * Return the resolved file path according to options.
     *
     * @param string $filePath
     * @param array  $options
     *
     * @return string
     */
    public function resolve($filePath, $options = []);
}
