<?php
namespace Oro\Bundle\SearchBundle\Engine\Orm\PdoPgsql;

use Doctrine\ORM\Query\Lexer;
use Doctrine\ORM\Query\AST\Functions\FunctionNode;

/**
 * "TsRank" "(" {StateFieldPathExpression ","}* InParameter ")"
 */
class TsRank extends FunctionNode
{
    public $columns = array();
    public $needle;
    public $mode;

    /**
     * Parse parameters
     *
     * @param \Doctrine\ORM\Query\Parser $parser
     */
    public function parse(\Doctrine\ORM\Query\Parser $parser)
    {
        $parser->match(Lexer::T_IDENTIFIER);
        $parser->match(Lexer::T_OPEN_PARENTHESIS);

        do {
            $this->columns[] = $parser->StateFieldPathExpression();
            $parser->match(Lexer::T_COMMA);
        } while ($parser->getLexer()->isNextToken(Lexer::T_IDENTIFIER));

        $this->needle = $parser->InParameter();

        $parser->match(Lexer::T_CLOSE_PARENTHESIS);
    }

    /**
     * Create sql string
     *
     * @param \Doctrine\ORM\Query\SqlWalker $sqlWalker
     *
     * @return string
     */
    public function getSql(\Doctrine\ORM\Query\SqlWalker $sqlWalker)
    {
        $haystack = null;

        $first = true;
        foreach ($this->columns as $column) {
            $first ? $first = false : $haystack .= ', ';
            $haystack .= $column->dispatch($sqlWalker);
        }

        return 'ts_rank(to_tsvector(' . $haystack . ') , to_tsquery(' . $this->needle->dispatch($sqlWalker) . '))';
    }
}
