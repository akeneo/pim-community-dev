<?php

namespace Akeneo\Component\FileStorage;

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
            while (!feof($resource)) {
                $buffer = fread($resource, StreamedFileResponse::CHUNK);
                echo $buffer;
                ob_flush();
            }
            fclose($resource);
        };

        $headers['Content-Type'] = 'application/octet-stream';

        parent::__construct($callback, $status, $headers);
    }
}
