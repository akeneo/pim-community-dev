<?php

namespace Oro\Bundle\NotificationBundle\Provider;

use Symfony\Component\Finder\Finder;
use Symfony\Component\HttpKernel\KernelInterface;

/**
 * Extracts event names from a php files.
 *
 */
class EventNamesExtractor
{
    const MESSAGE_TOKEN = 300;
    const IGNORE_TOKEN = 400;
    const EVENTS_PREFIX = 'oro.event.';

    /**
     * Found/extracted event names
     *
     * @var array
     */
    protected $eventNames = array();

    /**
     * The sequence that captures translation messages.
     *
     * @var array
     */
    protected $sequences = array(
        array(
            '->',
            'dispatch',
            '(',
            self::MESSAGE_TOKEN,
            ',',
        ),
    );

    public function __construct(KernelInterface $kernel)
    {
        $directories = false;
        foreach ($kernel->getBundles() as $bundle) {
            if (substr($bundle->getName(), 0, 3) == 'Oro') {
                /** @var $bundle \Symfony\Component\HttpKernel\Bundle\BundleInterface  */
                $directories[] = $bundle->getPath();
            }
        }

        $this->directories = $directories;
    }

    /**
     * Extract event names and return them in array
     *
     * @param string|null $directory
     * @return array
     */
    public function extract($directory = null)
    {
        if (!is_null($directory) && file_exists($directory)) {
            $this->directories = array($directory.'../../');
        }

        $finder = new Finder();
        foreach ($this->directories as $i => $directory) {
            //echo '[' . sprintf('%0.2f', 100*($i+1) / count($this->directories)) . '] ' . $directory . "\n";
            $files = $finder->files()->name('*.php')->in($directory);
            foreach ($files as $file) {
                $this->parseTokens(token_get_all(file_get_contents($file)));
            }
        }

        return $this->eventNames;
    }

    /**
     * Normalizes a token.
     *
     * @param mixed $token
     * @return string
     */
    protected function normalizeToken($token)
    {
        if (is_array($token)) {
            return $token[1];
        }

        return $token;
    }

    /**
     * Extracts trans message from php tokens.
     *
     * @param array            $tokens
     */
    protected function parseTokens($tokens)
    {
        foreach ($tokens as $key => $token) {
            foreach ($this->sequences as $sequence) {
                $message = '';

                foreach ($sequence as $id => $item) {
                    if ($this->normalizeToken($tokens[$key + $id]) == $item) {
                        continue;
                    } elseif (self::MESSAGE_TOKEN == $item) {
                        $message = $this->normalizeToken($tokens[$key + $id]);
                    } elseif (self::IGNORE_TOKEN == $item) {
                        continue;
                    } else {
                        break;
                    }
                }

                $message = trim($message, '\'');
                if ($message && substr($message, 0, strlen(self::EVENTS_PREFIX)) == self::EVENTS_PREFIX) {
                    $this->eventNames[$message] = $message;
                    break;
                }
            }
        }
    }
}
