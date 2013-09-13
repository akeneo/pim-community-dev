<?php

namespace Oro\Bundle\UserBundle\ImportExport\Serializer\Normalizer;

use Symfony\Component\Serializer\Exception\RuntimeException;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

use Oro\Bundle\UserBundle\Entity\User;

class UserNormalizer implements NormalizerInterface, DenormalizerInterface
{
    const FULL_MODE  = 'full';
    const SHORT_MODE = 'short';
    const USER_TYPE  = 'Oro\Bundle\UserBundle\Entity\User';

    private static $modes = array(self::FULL_MODE, self::SHORT_MODE);

    /**
     * @param User $object
     * @param mixed $format
     * @param array $context
     * @return array
     */
    public function normalize($object, $format = null, array $context = array())
    {
        $method = 'normalize' . ucfirst($this->getMode($context));
        return $this->$method($object, $format, $context);
    }

    /**
     * @param User $object
     * @param mixed $format
     * @param array $context
     * @return array
     */
    protected function normalizeShort($object, $format = null, array $context = array())
    {
        return array(
            'firstName' => $object->getFirstname(),
            'lastName' => $object->getLastname(),
        );
    }

    protected function normalizeFull($object, $format = null, array $context = array())
    {
        throw new RuntimeException('Full normalization is not implemented.');
    }

    /**
     * @param mixed $data
     * @param string $class
     * @param mixed $format
     * @param array $context
     * @return User
     */
    public function denormalize($data, $class, $format = null, array $context = array())
    {
        $method = 'denormalize' . ucfirst($this->getMode($context));
        return $this->$method($data, $class, $format, $context);
    }

    /**
     * @param mixed $data
     * @param string $class
     * @param mixed $format
     * @param array $context
     * @return User
     */
    protected function denormalizeShort($data, $class, $format = null, array $context = array())
    {
        $result = new User();
        if (!empty($data['firstName'])) {
            $result->setFirstname($data['firstName']);
        }
        if (!empty($data['lastName'])) {
            $result->setLastname($data['lastName']);
        }
        return $result;
    }

    protected function denormalizeFull($object, $format = null, array $context = array())
    {
        throw new RuntimeException('Full denormalization is not implemented.');
    }

    /**
     * {@inheritdoc}
     */
    public function supportsNormalization($data, $format = null)
    {
        return $data instanceof User;
    }

    /**
     * {@inheritdoc}
     */
    public function supportsDenormalization($data, $type, $format = null)
    {
        return is_array($data) && $type == static::USER_TYPE;
    }

    /**
     * @param array $context
     * @return string
     * @throws RuntimeException
     */
    protected function getMode(array $context)
    {
        $mode = isset($context['mode']) ? $context['mode'] : self::FULL_MODE;
        if (!in_array($mode, self::$modes)) {
            throw new RuntimeException(sprintf('Mode "%s" is not supported', $mode));
        }
        return $mode;
    }
}
