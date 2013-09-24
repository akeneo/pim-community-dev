<?php

namespace Oro\Bundle\ImportExportBundle\Serializer\Normalizer;

use Symfony\Component\Serializer\Exception\RuntimeException;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

abstract class AbstractContextModeAwareNormalizer implements NormalizerInterface, DenormalizerInterface
{
    /**
     * @var array
     */
    protected $availableModes = array();

    /**
     * @var string
     */
    protected $defaultMode = null;

    public function __construct(array $availableModes, $defaultMode = null)
    {
        $this->setAvailableModes($availableModes);
        if (null !== $defaultMode) {
            $this->setDefaultMode($defaultMode);
        }
    }

    /**
     * Normalization depends on mode
     *
     * @param mixed $object
     * @param mixed $format
     * @param array $context
     * @return array
     * @throws RuntimeException
     */
    public function normalize($object, $format = null, array $context = array())
    {
        $mode = $this->getMode($context);
        $method = 'normalize' . ucfirst($mode);
        if (method_exists($this, $method)) {
            return $this->$method($object, $format, $context);
        }
        throw new RuntimeException(sprintf('Normalization with mode "%s" is not supported', $mode));
    }

    /**
     * Denormalization depends on mode
     *
     * @param mixed $data
     * @param string $class
     * @param mixed $format
     * @param array $context
     * @return mixed
     * @throws RuntimeException
     */
    public function denormalize($data, $class, $format = null, array $context = array())
    {
        $mode = $this->getMode($context);
        $method = 'denormalize' . ucfirst($mode);
        if (method_exists($this, $method)) {
            return $this->$method($data, $class, $format, $context);
        }
        throw new RuntimeException(sprintf('Denormalization with mode "%s" is not supported', $mode));
    }

    /**
     * @param array $context
     * @return string
     * @throws RuntimeException
     */
    protected function getMode(array $context)
    {
        $mode = isset($context['mode']) ? $context['mode'] : $this->defaultMode;
        if (!in_array($mode, $this->availableModes)) {
            throw new RuntimeException(sprintf('Mode "%s" is not supported', $mode));
        }
        return $mode;
    }

    /**
     * @param array $modes
     * @return AbstractContextModeAwareNormalizer
     * @throws RuntimeException
     */
    protected function setAvailableModes(array $modes)
    {
        if (!$modes) {
            throw new RuntimeException(sprintf('Modes must an array with at least one element', $modes));
        }
        $this->availableModes = $modes;
        $this->setDefaultMode(reset($modes));
        return $this;
    }

    /**
     * @param string $mode
     * @return AbstractContextModeAwareNormalizer
     * @throws RuntimeException
     */
    protected function setDefaultMode($mode)
    {
        if (!in_array($mode, $this->availableModes)) {
            throw new RuntimeException(
                sprintf(
                    'Mode "%s" is not supported, available modes are "%s"',
                    $mode,
                    implode('", ', $this->availableModes)
                )
            );
        }
        $this->defaultMode = $mode;
        return $this;
    }
}
