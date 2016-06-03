<?php

namespace Pim\Component\Connector;

use Symfony\Component\Filesystem\Filesystem;

class WorkingDirectory
{
    /** @var \SplFileInfo */
    protected $handler;

    public function __construct()
    {
        $path = sys_get_temp_dir() . DIRECTORY_SEPARATOR . uniqid('akeneo_connector_');

        $fs = new Filesystem();
        $fs->mkdir($path);

        $this->handler = new \SplFileInfo($path);
    }

    /**
     * @return string
     */
    public function getPathname()
    {
        return $this->handler->getPathname();
    }

    /**
     * @return \SplFileInfo
     */
    public function getHandler()
    {
        return $this->handler;
    }

    public function remove()
    {
        $this->destroy();
    }

    public function __destruct()
    {
        $this->destroy();
    }

    private function destroy()
    {
        if (null === $this->handler) {
            return;
        }

        $path = $this->handler->getPathname();
        $fs = new Filesystem();

        if ($fs->exists($path)) {
            $fs->remove($path);
        }

        $this->handler = null;
    }
}
