<?php

namespace Pim\Bundle\UIBundle\Twig;

/**
 * Extension to load random user friendly messages instead of "Loading" message
 *
 * @author    Marie Minasyan <marie.minasyan@akeneo.com>
 * @copyright 2016 Akeneo SAS (http://www.akeneo.com)
 */
class LoadingMessageExtension extends \Twig_Extension
{
    /** @var string */
    protected $file;

    /**
     * @param string $file
     */
    public function __construct($file)
    {
        $this->file = $file;
    }

    /**
     * {@inheritdoc}
     */
    public function getFunctions()
    {
        return [
            'loading_message' => new \Twig_Function_Method($this, 'loadingMessage'),
        ];
    }

    /**
     * Return a random string from available messages list
     *
     * @return string
     */
    public function loadingMessage()
    {
        $messages = file($this->file);

        return $messages[array_rand($messages)];
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'pim_ui_loading_message_extension';
    }
}
