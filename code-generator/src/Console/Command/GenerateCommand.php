<?php
declare(strict_types=1);

namespace Tasuku43\CodeGenerator\Console\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Finder\Finder;
use Tasuku43\CodeGenerator\TemplateEngine\Twig\TwigTemplateEngine;

class GenerateCommand extends Command
{
    protected function configure()
    {
        $this->setName('generate')
            ->setDescription('generate sample code.')
            ->addOption(
                'usecaseName',
                null,
                InputOption::VALUE_REQUIRED,
                'UseCase name'
            )->addOption(
                'namespaceName',
                null,
                InputOption::VALUE_REQUIRED,
                'Namespace name'
            )->addOption(
                'outputPath',
                null,
                InputOption::VALUE_REQUIRED,
                'Namespace name'
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $usecaseName   = $input->getOption('usecaseName');
        $namespaceName = $input->getOption('namespaceName');
        $outputPath    = $input->getOption('outputPath');

        $template_engine = TwigTemplateEngine::newInstance();

        $finder = new Finder();
        $finder->in(__DIR__ . '/../../TemplateEngine/Twig/templates/clean-like')->name('*.twig')->files();
        foreach ($finder as $templateFile) {
            // テンプレートからPHPコードを生成
            $contents = $template_engine->render($templateFile->getFilename(), $usecaseName, $namespaceName);

            // ファイル名を解決
            $fileName = preg_replace(
                "/\.twig/", ".php", $usecaseName . $templateFile->getFilename()
            );

            // ファイルを出力する
            file_put_contents($outputPath . $fileName, $contents);
        }

        $output->writeln('Generate code success.');

        return self::SUCCESS;
    }
}
