<?php
declare(strict_types=1);

namespace Tasuku43\NamespaceAppender\NodeVisitor;

use PhpParser\Builder\Namespace_;
use PhpParser\Node;
use PhpParser\NodeVisitorAbstract;

class AddNamespaceVisitor extends NodeVisitorAbstract
{
    public function __construct(private string $namespace)
    {
    }

    public function leaveNode(Node $node)
    {
        if ($node instanceof Node\Stmt\Class_) {
            $namespaceBuilder = new Namespace_($this->namespace);

            // ClassノードをNamespaceノードに包んでstmtsに追加する
            $namespaceBuilder->addStmt($node);
            return $namespaceBuilder->getNode();
        }
        return null;
    }
}
