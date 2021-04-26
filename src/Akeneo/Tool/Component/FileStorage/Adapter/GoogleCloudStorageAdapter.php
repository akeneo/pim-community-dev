<?php

declare(strict_types=1);

namespace Akeneo\Tool\Component\FileStorage\Adapter;

use League\Flysystem\Util;
use Superbalist\Flysystem\GoogleStorage\GoogleStorageAdapter;

/**
 * @copyright 2021 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 * Override of Superbalist\Flysystem\GoogleStorage\GoogleStorageAdapter
 * It allows to take the `$recursive` argument of the `listContents()` method into account
 * The code is adapted from https://github.com/thephpleague/flysystem-google-cloud-storage/blob/2.x/GoogleCloudStorageAdapter.php,
 * which is unfortunately only compatible with flysystem v2
 */
final class GoogleCloudStorageAdapter extends GoogleStorageAdapter
{
    public function listContents($directory = '', $recursive = false): array
    {
        $prefixedPath = $this->applyPathPrefix($directory);
        $options = ['prefix' => rtrim($prefixedPath, '/') . '/'];

        if (false === $recursive) {
            $options['delimiter'] = '/';
            $options['includeTrailingDelimiter'] = true;
        }
        $prefixes = [];

        $objects = $this->bucket->objects($options);

        $normalised = [];
        foreach ($objects as $object) {
            $normalised[] = $this->normaliseObject($object);
            $prefixes[$this->removePathPrefix($object->name())] = true;
        }

        foreach ($objects->prefixes() as $prefix) {
            $prefix = $this->removePathPrefix($prefix);
            if (array_key_exists($prefix, $prefixes)) {
                continue;
            }
            $prefixes[$prefix] = true;
            $normalised[] = [
                'type' => 'dir',
                'path' => $prefix,
                'dirname' => \dirname($prefix),
                'basename' => \basename($prefix),
                'filename' => \basename($prefix),
            ];
        }

        return self::emulateDirectories($normalised);
    }

    private static function emulateDirectories(array $objects): array
    {
        $additionalDirectories = [];
        foreach ($objects as $object) {
            if ('file' !== ($object['type'] ?? null)) {
                continue;
            }

            $dirname = Util::dirname($object['path']);
            while ('' !== $dirname) {
                if (isset($additionalDirectories[$dirname])) {
                    break;
                }
                $additionalDirectories[$dirname] = [
                    'type' => 'dir',
                    'path' => $dirname,
                    'dirname' => Util::dirname($dirname),
                    'basename' => \basename($dirname),
                    'filename' => \basename($dirname),
                ];
                $dirname = Util::dirname($dirname);
            }
        }

        return \array_merge($objects, \array_values($additionalDirectories));
    }
}
