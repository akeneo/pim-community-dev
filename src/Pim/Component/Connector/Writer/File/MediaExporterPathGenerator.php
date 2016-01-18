<?php

namespace Pim\Component\Connector\Writer\File;

use Doctrine\Common\Util\ClassUtils;
use Pim\Bundle\CatalogBundle\Model\ProductValueInterface;

/**
 * Generate the path of medias to export.
 *
 * @author    Julien Janvier <jjanvier@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class MediaExporterPathGenerator implements FileExporterPathGeneratorInterface
{
    /**
     * {@inheritdoc}
     */
    public function generate($value, array $options = [])
    {
        if (!$value instanceof ProductValueInterface) {
            throw new \InvalidArgumentException(
                sprintf(
                    'Expects an "%s", "%s" provided.',
                    'Pim\Bundle\CatalogBundle\Model\ProductValueInterface',
                    ClassUtils::getClass($value)
                )
            );
        }

        if (null === $file = $value->getMedia()) {
            return '';
        }

        $attribute = $value->getAttribute();

        $identifier = $options['identifier'];
        $identifier = null !== $identifier ? $identifier : $value->getEntity()->getIdentifier();
        $target = sprintf('files/%s/%s', $identifier, $attribute->getCode());

        if ($attribute->isLocalizable()) {
            $target .= DIRECTORY_SEPARATOR . $value->getLocale();
        }
        if ($attribute->isScopable()) {
            $target .= DIRECTORY_SEPARATOR . $value->getScope();
        }

        return $target . DIRECTORY_SEPARATOR . $file->getOriginalFilename();
    }
}
