<?php

namespace Pim\Bundle\CatalogBundle\Manager;

use Pim\Bundle\CatalogBundle\Model\ProductTemplateInterface;
use Pim\Bundle\CatalogBundle\Model\ProductValueInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

/**
 * Product template media manager
 *
 * @author    Filips Alpe <filips@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ProductTemplateMediaManager
{
    /** @var MediaManager */
    protected $mediaManager;

    /** @var NormalizerInterface */
    protected $normalizer;

    /**
     * @param MediaManager        $mediaManager
     * @param NormalizerInterface $normalizer
     */
    public function __construct(MediaManager $mediaManager, NormalizerInterface $normalizer)
    {
        $this->mediaManager = $mediaManager;
        $this->normalizer   = $normalizer;
    }

    /**
     * Handles the media of the given product template
     *
     * @param ProductTemplateInterface $template
     */
    public function handleProductTemplateMedia(ProductTemplateInterface $template)
    {
        $mediaHandled = false;
        foreach ($template->getValues() as $value) {
            if (null !== $media = $value->getMedia()) {
                $mediaHandled = true;
                $filenamePrefix = $media->getFile() ? $this->generateFilenamePrefix($value) : null;
                $this->mediaManager->handle($media, $filenamePrefix);
            }
        }

        if ($mediaHandled) {
            $this->updateNormalizedValues($template);
        }
    }

    /**
     * @param ProductValueInterface $value
     *
     * @return string
     */
    public function generateFilenamePrefix(ProductValueInterface $value)
    {
        return sprintf(
            '%s-%s-%s-%s-%s',
            uniqid(),
            $value->getAttribute()->getCode(),
            $value->getLocale(),
            $value->getScope(),
            time()
        );
    }

    /**
     * Updates normalized product template values (required after handling new media added to a template)
     *
     * @param ProductTemplateInterface $template
     */
    protected function updateNormalizedValues(ProductTemplateInterface $template)
    {
        $valuesData = $this->normalizer->normalize($template->getValues(), 'json', ['entity' => 'product']);
        $template->setValuesData($valuesData);
    }
}
