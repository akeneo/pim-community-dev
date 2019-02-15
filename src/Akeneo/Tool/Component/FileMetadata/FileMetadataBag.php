<?php

/**
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2015 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Akeneo\Tool\Component\FileMetadata;

/**
 * FileMetadataBag is a container for recursive key/value pairs.
 *
 * @author    Adrien Pétremann <adrien.petremann@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 */
class FileMetadataBag implements FileMetadataBagInterface
{
    /** @var array */
    protected $data;

    /**
     * @param array $data
     */
    public function __construct(array $data = [])
    {
        $this->data = $data;
    }

    /**
     * {@inheritdoc}
     */
    public function add(array $data)
    {
        $this->data = array_replace_recursive($this->data, $data);
    }

    /**
     * {@inheritdoc}
     */
    public function get($path, $default = null)
    {
        $data = $this->data;

        // "exif.COMPUTED.Thumbnail\.Author" => ['exif', 'COMPUTED', 'Thumbnail\.Author']
        $keys = preg_split('/(?<!\\\)[.]/', $path);

        foreach ($keys as $key) {
            $key = strtolower(str_replace('\.', '.', $key));
            $data = array_change_key_case($data);

            if (!array_key_exists($key, $data)) {
                return $default;
            }

            $data = $data[$key];
        }

        if (is_array($data)) {
            $data = array_map(function ($value) {
                return $this->sanitizeNonStandardAsciiCharacters($value);
            }, $data);
        } else {
            $data = $this->sanitizeNonStandardAsciiCharacters($data);
        }

        return $data;
    }

    /**
     * {@inheritdoc}
     */
    public function has($path)
    {
        return null !== $this->get($path);
    }

    /**
     * {@inheritdoc}
     */
    public function all()
    {
        return $this->data;
    }

    /**
     * Remove non standard ascii characters between 20 and FF (in hexadecimal)
     *
     * @param $value
     *
     * @return mixed
     */
    private function sanitizeNonStandardAsciiCharacters($value)
    {
        if (! is_string($value)) {
            return $value;
        }

        return preg_replace('~[^\x20-\xFF]~ui', '', $value);
    }
}
