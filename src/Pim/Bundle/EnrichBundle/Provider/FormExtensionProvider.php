<?php

namespace Pim\Bundle\EnrichBundle\Provider;

/**
 * Form extension provider
 *
 * @author    Filips Alpe <filips@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class FormExtensionProvider
{
    /** @var array */
    protected $extensions = [];

    /**
     * @param array $extensions
     */
    public function setExtensions(array $extensions)
    {
        $this->extensions = $extensions;
    }

    /**
     * @param string $type
     *
     * @return array
     */
    public function getExtensions($type)
    {
        return isset($this->extensions[$type]) ? $this->extensions[$type] : [];
    }
}
