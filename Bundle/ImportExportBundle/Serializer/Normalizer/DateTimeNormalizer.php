<?php

namespace Oro\Bundle\ImportExportBundle\Serializer\Normalizer;

use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Component\Serializer\Exception\RuntimeException;

class DateTimeNormalizer implements NormalizerInterface, DenormalizerInterface
{
    /**
     * @var string
     */
    protected $defaultFormat;

    /**
     * @var \DateTimeZone
     */
    protected $defaultTimezone;

    public function __construct($defaultFormat = \DateTime::ISO8601, $defaultTimezone = 'UTC')
    {
        $this->defaultFormat = $defaultFormat;
        $this->defaultTimezone = new \DateTimeZone($defaultTimezone);
    }

    /**
     * @param \DateTime $object
     * @param mixed $format
     * @param array $context
     * @return string
     */
    public function normalize($object, $format = null, array $context = array())
    {
        return $object->format($this->getFormat($context));
    }

    /**
     * @param mixed $data
     * @param string $class
     * @param mixed $format
     * @param array $context
     * @return \DateTime
     * @throws RuntimeException
     */
    public function denormalize($data, $class, $format = null, array $context = array())
    {
        $timezone = $this->getTimezone($context);
        $format = $this->getFormat($context);
        $datetime = \DateTime::createFromFormat($format, (string) $data, $timezone);
        if (false === $datetime) {
            throw new RuntimeException(sprintf('Invalid datetime "%s", expected format %s.', $data, $format));
        }

        return $datetime;
    }

    /**
     * {@inheritdoc}
     */
    public function supportsNormalization($data, $format = null)
    {
        return $data instanceof \DateTime;
    }

    /**
     * {@inheritdoc}
     */
    public function supportsDenormalization($data, $type, $format = null)
    {
        return is_string($data) && $type == 'DateTime';
    }

    /**
     * @return string
     * @param array $context
     */
    protected function getFormat(array $context)
    {
        return isset($context['format']) ? $context['format'] : $this->defaultFormat;
    }

    /**
     * @return string
     * @param array $context
     */
    protected function getTimezone(array $context)
    {
        return isset($context['timezone']) ? $context['timezone'] : $this->defaultTimezone;
    }
}
