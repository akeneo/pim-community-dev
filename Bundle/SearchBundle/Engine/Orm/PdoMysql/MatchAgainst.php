<?php
namespace Oro\Bundle\SearchBundle\Engine\Orm\PdoMysql;

use Doctrine\ORM\Query\Lexer;
use Doctrine\ORM\Query\AST\Functions\FunctionNode;

/**
 * "MATCH_AGAINST" "(" {StateFieldPathExpression ","}* InParameter {Literal}? ")"
 */
class MatchAgainst extends FunctionNode
{
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

        while ($parser->getLexer()->isNextToken(Lexer::T_STRING)) {
            $this->mode = $parser->Literal();
        }

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

        $query = "MATCH(" . $haystack .
            ") AGAINST (" . $this->needle->dispatch($sqlWalker);

        if ($this->mode) {

            $query .= " " . str_replace('\'', '', $this->mode->dispatch($sqlWalker)) . " )";
        } else {
            $query .= " )";
        }

        return $query;
    }
}
