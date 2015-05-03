<?php
namespace DamEnterprise\Component\Asset\Model;

interface FileInterface
{
    /**
     * @return int
     */
    public function getId();

    /**
     * @return string
     */
    public function getPath();

    /**
     * @param string $path
     *
     * @return FileInterface
     */
    public function setPath($path);

    /**
     * @return string
     */
    public function getGuid();

    /**
     * @param string $guid
     *
     * @return FileInterface
     */
    public function setGuid($guid);

    /**
     * @return string
     */
    public function getFilename();

    /**
     * @param string $filename
     *
     * @return FileInterface
     */
    public function setFilename($filename);

    /**
     * @return string
     */
    public function getOriginalFilename();

    /**
     * @param string $originalFilename
     *
     * @return FileInterface
     */
    public function setOriginalFilename($originalFilename);

    /**
     * @return string
     */
    public function getMimeType();

    /**
     * @param string $mimeType
     *
     * @return FileInterface
     */
    public function setMimeType($mimeType);

    /**
     * @return int
     */
    public function getSize();

    /**
     * @param int $size
     *
     * @return FileInterface
     */
    public function setSize($size);

    /**
     * @return string
     */
    public function getStorage();

    /**
     * @param string $storage
     *
     * @return FileInterface
     */
    public function setStorage($storage);
}
