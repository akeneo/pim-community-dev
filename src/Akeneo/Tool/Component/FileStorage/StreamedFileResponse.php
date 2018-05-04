<?php

namespace Akeneo\Tool\Component\FileStorage;

use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * StreamedFileResponse
 *
 * @author    Julien Janvier <jjanvier@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class StreamedFileResponse extends StreamedResponse
{
    /** @staticvar int */
    const CHUNK = 1024;

    /**
     * @param resource $resource
     * @param int      $status
     * @param array    $headers
     */
    public function __construct($resource, $status = 200, $headers = [])
    {
        if (!is_resource($resource)) {
            throw new \InvalidArgumentException(sprintf('A resource is expected, "%s" given', gettype($resource)));
        }

        $callback = function () use ($resource) {
            $out = fopen('php://output', 'wb');

            stream_copy_to_stream($resource, $out);

            fclose($out);
            fclose($resource);
        };

        if (!isset($headers['Content-Type'])) {
            $headers['Content-Type'] = 'application/octet-stream';
        }

        parent::__construct($callback, $status, $headers);
    }
}
