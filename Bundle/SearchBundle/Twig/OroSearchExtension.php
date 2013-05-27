<?php
namespace Oro\Bundle\SearchBundle\Twig;

class OroSearchExtension extends \Twig_Extension
{
    public function getFilters()
    {
        return array(
            'highlight'      => new \Twig_Filter_Method($this, 'highlight'),
            'trim_string'    => new \Twig_Filter_Method($this, 'trimByString'),
            'highlight_trim' => new \Twig_Filter_Method($this, 'highlightTrim'),
        );
    }

    /**
     * Highlight search string words
     *
     * @param string $text
     * @param string $searchString
     *
     * @return string
     */
    public function highlight($text, $searchString)
    {
        $text = strip_tags($text);
        $searchString = $this->clearString($searchString);
        $searchArray = explode(' ', $searchString);
        foreach ($searchArray as $searchWord) {
            $text = preg_replace("/\p{L}*?" . preg_quote($searchWord) . "\p{L}*/ui", "<strong>$0</strong>", $text);
        }

        return $text;
    }

    /**
     * Trim text by search string
     *
     * @param string $text
     * @param string $searchString
     * @param int    $symbolCount
     *
     * @return string
     */
    public function trimByString($text, $searchString, $symbolCount = 400)
    {
        $searchString = $this->clearString($searchString);
        if (strpos($searchString, ' ') !== false) {
            $stringArray = explode(' ', $searchString);
            $searchString = $stringArray[0];
        }

        $strAfter = ' ' . substr(
            stristr($text, $searchString),
            0,
            strripos(substr(stristr($text, $searchString), 0, $symbolCount), ' ')
        ) . '...';
        $strBefore = '...' . substr(
            stristr($text, $searchString, true),
            0,
            strripos(substr(stristr($text, $searchString, true), 0, $symbolCount), ' ')
        );

        return strip_tags($strBefore . $strAfter);
    }

    /**
     * Trim and highlight text by search string
     *
     * @param     $text
     * @param     $searchString
     * @param int $symbolCount
     *
     * @return string
     */
    public function highlightTrim($text, $searchString, $symbolCount = 400)
    {
        return $this->highlight($this->trimByString($text, $searchString, $symbolCount), $searchString);
    }

    public function getName()
    {
        return 'search_extension';
    }

    /**
     * @param  string $inputString
     * @return string
     */
    private function clearString($inputString)
    {
        return trim(preg_replace('/[^a-z0-9\s]+/i', '', $inputString));
    }
}
