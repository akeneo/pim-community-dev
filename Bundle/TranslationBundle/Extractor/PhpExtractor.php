<?php

namespace Oro\Bundle\TranslationBundle\Extractor;

use Symfony\Component\Finder\Finder;
use Symfony\Bundle\FrameworkBundle\Translation\PhpExtractor as BaseExtractor;
use Symfony\Component\Translation\MessageCatalogue;

/**
 * Extracts strings for translation from php files
 */
class PhpExtractor extends BaseExtractor
{
    /** @var string */
    protected $prefix = '';

    /**
     * {@inheritDoc}
     */
    public function extract($directory, MessageCatalogue $catalog)
    {
        $finder = new Finder();
        $files = $finder->files()->name('*.php')->in($directory. '/../../')->exclude(array('Tests', 'Resources'));
        foreach ($files as $file) {
            $this->parseTokens(token_get_all(file_get_contents($file)), $catalog);
        }
    }

    /**
     * Extracts trans message from php tokens.
     *
     * @param array $tokens
     * @param MessageCatalogue $catalog
     */
    protected function parseTokens($tokens, MessageCatalogue $catalog)
    {
        $vendorName = $this->getVendorName($tokens);

        // trying to find messages for translation only in case if vendor name found
        if ($vendorName !== false) {
            foreach ($tokens as $token) {
                if (is_array($token) && $token[0] == T_CONSTANT_ENCAPSED_STRING) {
                    $message = $token[1];

                    $message = trim($message, '\'""');

                    if ($message) {
                        $messageToCheck = explode('.', $message);

                        if (count($messageToCheck) > 2 && strcmp($messageToCheck[0], $vendorName) === 0) {
                            $catalog->set($message, $this->prefix . $message);
                        }
                    }
                }
            }
        }
    }

    /**
     * @param $tokens
     * @return bool|string
     */
    protected function getVendorName($tokens)
    {
        $vendorName = false;

        foreach ($tokens as $k => $token) {
            if (is_array($token) && $token[0] == T_NS_SEPARATOR) {
                $vendorName = strtolower($tokens[$k - 1][1]);

                break;
            }
        }

        return $vendorName;
    }

    /**
     * {@inheritDoc}
     */
    public function setPrefix($prefix)
    {
        $this->prefix = $prefix;
    }
}
