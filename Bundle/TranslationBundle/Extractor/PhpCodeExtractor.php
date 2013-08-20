<?php

namespace Oro\Bundle\TranslationBundle\Extractor;

use Symfony\Component\Finder\Finder;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Translation\Extractor\ExtractorInterface;
use Symfony\Component\Translation\MessageCatalogue;

/**
 * Extracts strings for translation from php files
 */
class PhpCodeExtractor implements ExtractorInterface
{
    const MESSAGE_TOKEN = 400;

    /** @var string */
    protected $prefix = '';

    /** @var ContainerInterface */
    protected $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

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
                    $message = trim($message, '\'"');

                    if ($message) {
                        $messageToCheck = explode('.', $message);

                        if (count($messageToCheck) > 2
                            && strcmp($messageToCheck[0], $vendorName) === 0
                            && !$this->container->has($message)
                            && !$this->container->hasParameter($message)) {

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

        $sequence = array(
            'namespace',
            ' ',
            self::MESSAGE_TOKEN,
        );

        foreach ($tokens as $k => $token) {
            foreach ($sequence as $id => $item) {
                if ($this->normalizeToken($tokens[$k + $id]) == $item) {
                    continue;
                } elseif (self::MESSAGE_TOKEN == $item) {
                    $vendorName = strtolower($this->normalizeToken($tokens[$k + $id]));
                } else {
                    break;
                }
            }
        }

        return $vendorName;
    }

    /**
     * @param $token
     * @return mixed
     */
    protected function normalizeToken($token)
    {
        return is_array($token) ? $token[1] : $token;
    }

    /**
     * {@inheritDoc}
     */
    public function setPrefix($prefix)
    {
        $this->prefix = $prefix;
    }
}
