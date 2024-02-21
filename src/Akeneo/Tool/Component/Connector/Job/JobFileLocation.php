<?php

namespace Akeneo\Tool\Component\Connector\Job;

/**
 * Represents file location logic used for import or export job.
 *
 * This class is mostly a compatibility layer in term of interpretation
 * of file location string representation (URL): if this representation
 * doesn't have the remote schema prefix, then it's treated as a local
 * path, allowing already existing usage with local path to continue to
 * work.
 *
 * @author    Benoit Jacquemont <benoit@akeneo.com>
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class JobFileLocation
{
    private const REMOTE_SCHEMA = 'pim_remote://';

    private const LOCAL_TEMP_PREFIX = 'from_remote_';

    /** @var string */
    private $path;

    /** @var bool */
    private $remote;

    public function __construct(string $path, bool $remote)
    {
        $this->path = $path;
        $this->remote = $remote;
    }

    /**
     * Generate a JobFileLocation object from an JobFileLocation URL
     * If this URL doesn't have a schema, it means it's a local file.
     */
    public static function parseUrl(string $url): JobFileLocation
    {
        if (0 === strpos($url, self::REMOTE_SCHEMA)) {
            $remote = true;
            $path = substr($url, strlen(self::REMOTE_SCHEMA));
        } else {
            $remote = false;
            $path = $url;
        }

        return new self($path, $remote);
    }

    public function isRemote(): bool
    {
        return $this->remote;
    }

    public function path(): string
    {
        return $this->path;
    }

    /**
     * Return an URL for this location. If the location is remote,
     * the URL is the path prefixed with REMOTE_SCHEMA.
     * Otherwise, the encoded location is the path
     */
    public function url(): string
    {
        if (true === $this->remote) {
            return self::REMOTE_SCHEMA.$this->path;
        }

        return $this->path;
    }
}
