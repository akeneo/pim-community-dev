<?php

namespace Oro\Bundle\SecurityBundle\Encoder;

use ass\XmlSecurity\Key\Aes256Cbc as Origin;

class Mcrypt extends Origin
{
    public function __construct($key = null)
    {
        if ($key !== null && strlen($key) < 32) {
            // use hash in case when key length less than needed
            $key = md5($key);
        }
        parent::__construct($key);
    }

    /**
     * {@inheritdoc}
     */
    public function encryptData($data)
    {
        return base64_encode(parent::encryptData($data));
    }

    /**
     * {@inheritdoc}
     */
    public function decryptData($data)
    {
        return  str_replace("\x0", '', trim(parent::decryptData(base64_decode((string) $data))));
    }
}
