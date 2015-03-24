<?php

namespace Pim\Bundle\EnrichBundle\Controller;

use Pim\Bundle\EnrichBundle\Provider\FormExtensionProvider;
use Symfony\Component\HttpFoundation\JsonResponse;

/**
 * Form extension controller
 *
 * @author    Filips Alpe <filips@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class FormExtensionRestController
{
    /** @var FormExtensionProvider */
    protected $extensionProvider;

    /**
     * @param FormExtensionProvider $extensionProvider
     */
    public function __construct(FormExtensionProvider $extensionProvider)
    {
        $this->extensionProvider = $extensionProvider;
    }

    /**
     * @return JsonResponse
     */
    public function getAction()
    {
        return new JsonResponse($this->extensionProvider->getExtensions());
    }
}
