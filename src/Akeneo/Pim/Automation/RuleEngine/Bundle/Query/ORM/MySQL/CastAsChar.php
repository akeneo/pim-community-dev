<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2021 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Pim\Automation\RuleEngine\Bundle\Query\ORM\MySQL;

use Doctrine\ORM\Query\AST\Functions\FunctionNode;
use Doctrine\ORM\Query\AST\Node;
use Doctrine\ORM\Query\Lexer;
use Doctrine\ORM\Query\Parser;
use Doctrine\ORM\Query\SqlWalker;

/**
 * @todo @merge master/6.0: remove this class
 * This class is a dirty workaround because we cannot update the DB schema in 5.0, we need to rework the feature
 */
final class CastAsChar extends FunctionNode
{
    private Node $fieldIdentifierExpression;
    private ?string $collation = null;

    /**
     * "CASTASCHAR" "(" "fieldIdentifierExpression" ["," "$collation"] ")"
     *
     * @example SELECT CASTASCHAR(a.id, utf8mb4_unicode_ci) FROM a
     *
     * {@inheritdoc}
     */
    public function parse(Parser $parser): void
    {
        $parser->match(Lexer::T_IDENTIFIER);
        $parser->match(Lexer::T_OPEN_PARENTHESIS);
        $this->fieldIdentifierExpression = $parser->SimpleArithmeticExpression();
        if ($parser->getLexer()->isNextToken(Lexer::T_COMMA)) {
            $parser->match(Lexer::T_COMMA);
            $parser->match(Lexer::T_IDENTIFIER);
            $this->collation = $parser->getLexer()->token['value'];
        }

        $parser->match(Lexer::T_CLOSE_PARENTHESIS);
    }

    /**
     * {@inheritdoc}
     */
    public function getSql(SqlWalker $sqlWalker): string
    {
        $sql = sprintf(
            'CAST(%s AS CHAR)',
            $sqlWalker->walkSimpleArithmeticExpression($this->fieldIdentifierExpression),
        );
        if (null !== $this->collation) {
            $sql .= \sprintf(' COLLATE %s', $this->collation);
        }

        return $sql;
    }
}
