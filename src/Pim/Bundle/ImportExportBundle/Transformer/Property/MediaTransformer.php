<?php

namespace Pim\Bundle\ImportExportBundle\Transformer\Property;

use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpFoundation\File\Exception\FileNotFoundException;
use Oro\Bundle\FlexibleEntityBundle\Entity\Media;
use Pim\Bundle\CatalogBundle\Model\ProductValueInterface;
use Pim\Bundle\ImportExportBundle\Exception\InvalidValueException;


/**
 * Media attribute transformer
 *
 * @author    Antoine Guigan <antoine@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class MediaTransformer implements PropertyTransformerInterface, ProductValueUpdaterInterface
{
    /**
     * {@inheritdoc}
     */
    public function transform($value, array $options = array())
    {
        $value = trim($value);

        if (is_dir($value)) {
            return;
        }

        try {
            $file = new File($value);
        } catch (Exception $e) {
            throw new InvalidValueException('File not found: %value%', array('%value%' => $value));
        }

        return $file;
    }

    /**
     * {@inheritdoc}
     */
    public function updateProductValue(ProductValueInterface $productValue, $data, array $options = array())
    {
        if (null === $data) {
            return;
        }

        if (!$productValue->getMedia()) {
            $productValue->setMedia(new Media);
        }
        $productValue->getMedia()->setFile($data);
    }

}
