<?php

namespace Oro\Bundle\ImportExportBundle\Serializer\Encoder;

use Symfony\Component\Serializer\Encoder\EncoderInterface;
use Symfony\Component\Serializer\Encoder\DecoderInterface;

/**
 * This encoder is used by import/export processor, no encoding/decoding is required, all work is done by normalizers
 */
class DummyEncoder implements EncoderInterface, DecoderInterface
{
    /**
     * {@inheritdoc}
     */
    public function encode($data, $format, array $context = array())
    {
        return $data;
    }

    /**
     * {@inheritdoc}
     */
    public function decode($data, $format, array $context = array())
    {
        return $data;
    }

    /**
     * {@inheritdoc}
     */
    public function supportsEncoding($format)
    {
        return $format === null;
    }

    /**
     * {@inheritdoc}
     */
    public function supportsDecoding($format)
    {
        return $format === null;
    }
}
