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
    protected $defaultDateTimeFormat;

    /**
     * @var string
     */
    protected $defaultDateFormat;

    /**
     * @var string
     */
    protected $defaultTimeFormat;

    /**
     * @var \DateTimeZone
     */
    protected $defaultTimezone;

    public function __construct(
        $defaultDateTimeFormat = \DateTime::ISO8601,
        $defaultDateFormat = 'Y-m-d',
        $defaultTimeFormat = 'H:i:s',
        $defaultTimezone = 'UTC'
    ) {
        $this->defaultDateTimeFormat = $defaultDateTimeFormat;
        $this->defaultDateFormat = $defaultDateFormat;
        $this->defaultTimeFormat = $defaultTimeFormat;
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
        $datetime = \DateTime::createFromFormat($format . '|', (string) $data, $timezone);
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
        if (!empty($context['format'])) {
            return $context['format'];
        }

        if (!empty($context['type'])) {
            switch ($context['type']) {
                case 'date':
                    return $this->defaultDateFormat;
                case 'time':
                    return $this->defaultTimeFormat;
                default:
                    return $this->defaultDateTimeFormat;
            }
        }

        return $this->defaultDateTimeFormat;
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
