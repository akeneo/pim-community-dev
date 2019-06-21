<?php

declare(strict_types=1);

namespace Akeneo\Platform\Bundle\UIBundle\Controller\InternalApi;

use Symfony\Component\HttpFoundation\JsonResponse;

/**
 * @author JM Leroux <jean-marie.leroux@akeneo.com>
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class EnvironmentController
{
    public function getAction(): JsonResponse
    {
        return new JsonResponse(
            [
                'upload_max_file_size' => $this->serverMaxFileSize(),
            ]
        );
    }

    /**
     * @return int Upload max filesize in Mb
     */
    private function serverMaxFileSize(): int
    {
        $uploadMaxFilesize = trim(ini_get('upload_max_filesize'));

        if (is_numeric($uploadMaxFilesize)) {
            return (int) $uploadMaxFilesize;
        }

        $unit = strtolower($uploadMaxFilesize[strlen($uploadMaxFilesize) - 1]);
        $uploadMaxFilesize = (int) substr($uploadMaxFilesize, 0, -1);

        if ('g' === $unit) {
            $uploadMaxFilesize *= 1024;
        }

        return $uploadMaxFilesize;
    }
}
