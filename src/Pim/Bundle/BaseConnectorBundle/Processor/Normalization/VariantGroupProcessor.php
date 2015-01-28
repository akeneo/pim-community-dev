<?php

namespace Pim\Bundle\BaseConnectorBundle\Processor\Normalization;

use Akeneo\Bundle\BatchBundle\Item\AbstractConfigurableStepElement;
use Akeneo\Bundle\BatchBundle\Item\InvalidItemException;
use Akeneo\Bundle\BatchBundle\Item\ItemProcessorInterface;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Component\Validator\ValidatorInterface;

/**
 * Variant group export processor, allows to,
 *  - normalize variant groups and related values (media included)
 *  - return the normalized data
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class VariantGroupProcessor extends AbstractConfigurableStepElement implements ItemProcessorInterface
{
    /** @var NormalizerInterface */
    protected $normalizer;

    /** @var string */
    protected $uploadDirectory;

    /** @var string */
    protected $format;

    /**
     * @param NormalizerInterface $normalizer
     * @param string              $uploadDirectory
     * @param string              $format
     */
    public function __construct(NormalizerInterface $normalizer, $uploadDirectory, $format)
    {
        $this->normalizer      = $normalizer;
        $this->uploadDirectory = $uploadDirectory;
        $this->format          = $format;
    }

    /**
     * {@inheritdoc}
     */
    public function process($item)
    {
        $data['media'] = [];
        if (false /*count($item->getMedia()) > 0*/) { // TODO: getMedia() ... naze !
            try {
                $data['media'] = $this->normalizer->normalize(
                    $item->getMedia(),
                    $this->format,
                    ['field_name' => 'media', 'prepare_copy' => true]
                );
            } catch (FileNotFoundException $e) {
                throw new InvalidItemException(
                    $e->getMessage(),
                    [
                        'item'            => $item->getOriginalProduct()->getIdentifier()->getData(),
                        'uploadDirectory' => $this->uploadDirectory,
                    ]
                );
            }
        }

        $data['variant_group'] = $this->normalizer->normalize($item, $this->format, ['with_variant_group_values' => true]);

        return $data;
    }

    /**
     * {@inheritdoc}
     */
    public function getConfigurationFields()
    {
        return [];
    }
}
