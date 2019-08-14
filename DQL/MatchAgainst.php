<?php
// myBundle/Extensions/Doctrine/MatchAgainst.php

namespace SAM\CommonBundle\DQL;

use Doctrine\ORM\Query\AST\Functions\FunctionNode;
use Doctrine\ORM\Query\AST\InputParameter;
use Doctrine\ORM\Query\Lexer;
use Doctrine\ORM\Query\Parser;
use Doctrine\ORM\Query\SqlWalker;

/**
 * Class MatchAgainst
 */
class MatchAgainst extends FunctionNode
{
    /**
     * @var array
     */
    public $columns;

    /**
     * @var InputParameter
     */
    public $needle;

    /**
     * @var InputParameter
     */
    public $mode;

    /**
     * @var string
     */
    public $defaultMode;

    /**
     * MatchAgainst constructor.
     *
     * @param string $name
     */
    public function __construct($name)
    {
        parent::__construct($name);
        $this->columns = [];
        $this->defaultMode = 'BOOLEAN MODE';
    }

    /**
     * @param Parser $parser
     */
    public function parse(Parser $parser)
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
     * @param SqlWalker $sqlWalker
     *
     * @return string
     */
    public function getSql(SqlWalker $sqlWalker)
    {
        $chunks = array_map(function ($column) use ($sqlWalker) {
            return $column->dispatch($sqlWalker);
        }, $this->columns);

        $mode = sprintf('IN %s', $this->defaultMode);
        if ($this->mode) {
            $mode = $this->mode->dispatch($sqlWalker);
        }
        $query = sprintf(
            'MATCH(%s) AGAINST (%s %s)',
            implode(', ', $chunks),
            $this->needle->dispatch($sqlWalker),
            $mode
        );

        return $query;
    }
}
