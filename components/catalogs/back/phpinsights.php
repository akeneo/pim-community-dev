<?php

declare(strict_types=1);

// @see https://github.com/nunomaduro/phpinsights/blob/master/stubs/symfony.php

return [
    'preset' => 'symfony',
    'ide' => null,
    'exclude' => [
        'tests',
        'Infrastructure/Symfony',
    ],
    'add' => [
    ],
    'remove' => [
        /**
         * Requires a number of line between different annotations in comment. As we can't differentiate class and the others it always fails.
         */
        SlevomatCodingStandard\Sniffs\Commenting\DocCommentSpacingSniff::class,
        /**
         * Normal classes are forbidden. Classes must be final or abstract
         */
        NunoMaduro\PhpInsights\Domain\Insights\ForbiddenNormalClasses::class,

        /**
         * This sniff ensures there is a single space after a NOT operator.
         */
        PHP_CodeSniffer\Standards\Generic\Sniffs\Formatting\SpaceAfterNotSniff::class,

        SlevomatCodingStandard\Sniffs\Classes\SuperfluousInterfaceNamingSniff::class,
        SlevomatCodingStandard\Sniffs\Classes\SuperfluousExceptionNamingSniff::class,
        SlevomatCodingStandard\Sniffs\Classes\SuperfluousTraitNamingSniff::class,
        NunoMaduro\PhpInsights\Domain\Insights\ForbiddenTraits::class,
        SlevomatCodingStandard\Sniffs\ControlStructures\AssignmentInConditionSniff::class,
        SlevomatCodingStandard\Sniffs\ControlStructures\DisallowYodaComparisonSniff::class,
        SlevomatCodingStandard\Sniffs\TypeHints\DisallowMixedTypeHintSniff::class,
        SlevomatCodingStandard\Sniffs\Functions\UnusedParameterSniff::class,

        /**
         * Should we discuss?
         */
        SlevomatCodingStandard\Sniffs\ControlStructures\DisallowShortTernaryOperatorSniff::class,
    ],
    'config' => [
        PHP_CodeSniffer\Standards\Generic\Sniffs\Files\LineLengthSniff::class => [
            'lineLimit' => 180, // default is 80
            'absoluteLineLimit' => 180, // default is 80
        ],
        SlevomatCodingStandard\Sniffs\Functions\FunctionLengthSniff::class => [
            'maxLinesLength' => 150, // default is 20
        ],
        NunoMaduro\PhpInsights\Domain\Insights\CyclomaticComplexityIsHigh::class => [
            'maxComplexity' => 10, // default is 5
        ],
    ],
    'requirements' => [
//        'min-quality' => 0,
//        'min-complexity' => 0,
//        'min-architecture' => 0,
//        'min-style' => 0,
//        'disable-security-check' => false,
    ],
    'threads' => null,
];
