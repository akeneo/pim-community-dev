<?php

namespace Akeneo\Platform\Bundle\AnalyticsBundle\Command\Style;

use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Helper\TableStyle;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * Customizes the table rendering of the Symfony default command style.
 *
 * @author    Damien Carcel <damien.carcel@akeneo.com>
 * @copyright 2016 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class SystemInfoStyle extends SymfonyStyle
{
    /**
     * {@inheritdoc}
     */
    public function table(array $headers, array $rows)
    {
        $headers = array_map(
            function ($value) {
                return sprintf("<info>%s</info>", $value);
            },
            $headers
        );

        $styleGuide = new TableStyle();
        $styleGuide
            ->setHorizontalBorderChar('-')
            ->setVerticalBorderChar('|')
            ->setCrossingChar('+')
            ->setCellHeaderFormat('%s')
        ;

        $table = new Table($this);
        $table->setHeaders($headers);
        $table->setRows($rows);
        $table->setStyle($styleGuide);

        $table->render();
        $this->newLine();
    }
}
