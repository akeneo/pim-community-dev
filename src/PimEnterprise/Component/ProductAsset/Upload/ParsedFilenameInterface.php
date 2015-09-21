<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2015 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PimEnterprise\Component\ProductAsset\Upload;

/**
 * Parse an uploaded filename
 * Extract asset code and optionnal locale code.
 * Sanitize asset code.
 *
 * @author JM Leroux <jean-marie.leroux@akeneo.com>
 */
interface ParsedFilenameInterface
{
    /**
     * @return string
     */
    public function getRawFilename();

    /**
     * @return string
     */
    public function getAssetCode();

    /**
     * @return string
     */
    public function getLocaleCode();

    /**
     * @return string
     */
    public function getExtension();

    /**
     * Return a clean parsed and sanitized filename
     *
     * @return string
     */
    public function getCleanFilename();

    /**
     * @param string $rawFilename
     */
    public function parseRawFilename($rawFilename);
}
