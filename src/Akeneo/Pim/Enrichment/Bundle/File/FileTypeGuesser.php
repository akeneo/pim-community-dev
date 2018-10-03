<?php

namespace Akeneo\Pim\Enrichment\Bundle\File;

/**
 * Filetype guesser interface implementation
 *
 * @see https://www.iana.org/assignments/media-types/media-types.xhtml
 *
 * @author    Yohan Blain <yohan.blain@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class FileTypeGuesser implements FileTypeGuesserInterface
{
    /** @var array */
    protected $typesMapping;

    /**
     * @param array $extraTypesMapping optional extra types
     */
    public function __construct(array $extraTypesMapping = [])
    {
        $typesMapping = [
            FileTypes::DOCUMENT => [
                'application/msword',
                'application/pdf',
                'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
                'application/vnd.ms-excel',
                'application/x-pdf',
                'text/*'
            ],
            FileTypes::IMAGE => ['image/*'],
            FileTypes::VIDEO => ['video/*']
        ];

        $this->typesMapping = array_merge($typesMapping, $extraTypesMapping);
    }

    /**
     * {@inheritdoc}
     */
    public function guess($mimeType)
    {
        foreach ($this->typesMapping as $fileType => $mappedTypes) {
            foreach ($mappedTypes as $mappedType) {
                if ($mappedType === $mimeType) {
                    return $fileType;
                }

                if ($discrete = strstr($mappedType, '/*', true)) {
                    if (strstr($mimeType, '/', true) === $discrete) {
                        return $fileType;
                    }
                }
            }
        }

        return FileTypes::MISC;
    }
}
