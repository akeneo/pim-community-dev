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
        foreach ($tokens as $token) {
            if (is_array($token) && $token[0] == T_CONSTANT_ENCAPSED_STRING) {
                $message = $token[1];
                $message = trim($message, '\'"');

                if ($message) {
                    if (substr_count($message, '.') >= 2
                        && preg_match('#^[\w\d]+\.[\w\d]+\.[\w\d]+(\.[\w\d]+)?$#Ui', $message)
                        && !$this->container->has($message)) {

                        $catalog->set($message, $this->prefix . $message);
                    }
                }
            }
        }
    }

    /**
     * {@inheritDoc}
     */
    public function setPrefix($prefix)
    {
        $this->prefix = $prefix;
    }
}
