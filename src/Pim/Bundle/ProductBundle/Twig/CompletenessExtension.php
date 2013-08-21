<?php

namespace Pim\Bundle\ProductBundle\Twig;

class CompletenessExtension extends \Twig_Extension
{
    public function getFunctions()
    {
        return array(
            'healthBar' => new \Twig_Function_Method($this, 'healthBar')
        );
    }

    public function healthBar($ratio)
    {
        $rgbColor = $this->defineHealthBarColor($ratio);
        $hexaColor = $this->rgbToHexa($rgbColor);

        return '<div class="healthbar-container" style="-webkit-box-shadow: inset 0 2px 5px #AAA;">
            <div class="healthbar" style="width: '. $ratio .'%; background-color: '. $hexaColor .';">&nbsp;</div>
        </div>';
    }

    private function rgbToHexa(array $rgb)
    {
        $red   = $this->dec2Hexa($rgb[0]);
        $green = $this->dec2Hexa($rgb[1]);
        $blue  = $this->dec2Hexa($rgb[2]);

        return '#'.$red.$green.$blue;
    }

    private function dec2Hexa($dec)
    {
        return str_pad(dechex($dec), 2, "0", STR_PAD_LEFT);
    }

    protected function defineHealthBarColor($ratio, $max = 100)
    {
        $percent = $ratio;

        $green = round(($percent*255)/100);
        $red   = 255-$green;
        if ($percent <= 0) {
            return array(255, 0, 0);
        }

        return array($red, $green, 0);
    }

    public function getName()
    {
        return 'pim_completeness_extension';
    }
}
