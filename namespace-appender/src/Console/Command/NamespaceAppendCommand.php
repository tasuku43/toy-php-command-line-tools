<?php
declare(strict_types=1);

namespace Tasuku43\NamespaceAppender\Console\Command;

use PhpParser\NodeTraverser;
use PhpParser\ParserFactory;
use PhpParser\PrettyPrinter\Standard;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Finder\SplFileInfo;
use Tasuku43\NamespaceAppender\NodeVisitor\AddNamespaceVisitor;

class NamespaceAppendCommand extends Command
{
    protected function configure()
    {
        $this->setName('append')
            ->setDescription('Append Namespace')
            ->addOption(
                'path',
                null,
                InputOption::VALUE_REQUIRED,
                'Directory path.'
            )->addOption(
                'namespaceRootName',
                null,
                InputOption::VALUE_REQUIRED,
                'Namespace root name.'
            )->addOption(
                'namespaceRootPath',
                null,
                InputOption::VALUE_REQUIRED,
                'Namespace root directory.'
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $path              = $input->getOption('path');
        $namespaceRootName = $input->getOption('namespaceRootName');
        $namespaceRootPath = $input->getOption('namespaceRootPath');

        $finder = new Finder();
        // `namespace`がすでに存在するクラスを除外する
        $finder->in($path)->name('*.php')->notContains('namespace')->files();

        $parser = (new ParserFactory)->create(ParserFactory::PREFER_PHP7);

        $prettyPrinter = new Standard();

        foreach ($finder as $file) {
            // コンテンツをASTに変換
            $ast = $parser->parse($file->getContents());

            // namespaceを追加するVisitorクラスを追加し、ASTの構造を書き換える。
            $traverser = new NodeTraverser();
            $traverser->addVisitor(new AddNamespaceVisitor(
                $this->resolveNamespace($file, (string)$namespaceRootPath, (string)$namespaceRootName)
            ));
            $ast = $traverser->traverse($ast);

            // ファイルを上書きする
            file_put_contents($file->getPathname(), $prettyPrinter->prettyPrintFile($ast));
        }

        (new SymfonyStyle($input, $output))->success('Successfully appended namespace!');

        return self::SUCCESS;
    }

    private function resolveNamespace(SplFileInfo $file, string $namespaceRootPath, string $namespaceRootName): string
    {
        // Namespaceルートのパスからの相対パスを取得
        $relativeNamespace = ltrim(ltrim($file->getPath(), $namespaceRootPath), "/");
        return $namespaceRootName . "\\" . preg_replace("/\//", "\\", $relativeNamespace);
    }
}
