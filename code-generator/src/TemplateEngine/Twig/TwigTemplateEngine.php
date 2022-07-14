<?php
declare(strict_types=1);

namespace Tasuku43\CodeGenerator\TemplateEngine\Twig;

use Twig\Environment;
use Twig\Loader\FilesystemLoader;

class TwigTemplateEngine
{
    private const TEMPLATE_DIR = __DIR__ . '/templates/clean-like';

    public function __construct(private Environment $twig)
    {
    }

    public static function newInstance(): self
    {
        return new self(new Environment(new FilesystemLoader(self::TEMPLATE_DIR)));
    }

    public function render(string $templateName, string $usecaseName, string $namespaceName): string
    {
        return $this->twig->render($templateName, [
            'usecaseName' => $usecaseName,
            'namespaceName' => $namespaceName
        ]);
    }
}
