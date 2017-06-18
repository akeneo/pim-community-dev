<?php

namespace Pim\Bundle\UIBundle\Twig;

use Symfony\Component\HttpKernel\Config\FileLocator;

/**
 * Extension to load random user friendly messages instead of "Loading" message
 *
 * @author    Marie Minasyan <marie.minasyan@akeneo.com>
 * @copyright 2016 Akeneo SAS (http://www.akeneo.com)
 */
class LoadingMessageExtension extends \Twig_Extension
{
    /** @var FileLocator */
    protected $fileLocator;

    /** @var string */
    protected $file;

    /**
     * @param string $file
     */
    public function __construct(FileLocator $fileLocator, $file)
    {
        $this->fileLocator = $fileLocator;
        $this->file = $file;
    }

    /**
     * {@inheritdoc}
     */
    public function getFunctions()
    {
        return [
            new \Twig_SimpleFunction('loading_message', [$this, 'loadingMessage']),
        ];
    }

    /**
     * Returns a random string from available messages list
     *
     * @return string
     */
    public function loadingMessage()
    {
        $path = $this->fileLocator->locate($this->file);
        $messages = file($path);

        return $messages[array_rand($messages)];
    }
}
