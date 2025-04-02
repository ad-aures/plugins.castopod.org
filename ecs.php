<?php

declare(strict_types=1);

use PhpCsFixer\Fixer\ClassNotation\ClassAttributesSeparationFixer;
use PhpCsFixer\Fixer\ClassNotation\VisibilityRequiredFixer;
use PhpCsFixer\Fixer\ControlStructure\TrailingCommaInMultilineFixer;
use PhpCsFixer\Fixer\Operator\BinaryOperatorSpacesFixer;
use PhpCsFixer\Fixer\Whitespace\StatementIndentationFixer;
use Symplify\EasyCodingStandard\Config\ECSConfig;

return ECSConfig::configure()
    ->withPaths([__DIR__ . '/app', __DIR__ . '/tests'])
    ->withRootFiles()
    ->withPreparedSets(cleanCode: true, common: true, symplify: true, psr12: true, strict: true)
    ->withConfiguredRule(BinaryOperatorSpacesFixer::class, [
        'operators' => [
            '=>' => 'align_single_space_minimal',
        ],
    ])
    ->withConfiguredRule(TrailingCommaInMultilineFixer::class, [
        'elements' => ['arguments', 'array_destructuring', 'arrays', 'match', 'parameters'],
    ])
    // TODO: remove when PHPCSFixer gets 8.4 support
    ->withSkip([
        ClassAttributesSeparationFixer::class => [
            'app/Entities/Person.php',
            'app/Libraries/PluginRepositoryCrawler.php',
        ],
        VisibilityRequiredFixer::class    => ['app/Entities/Person.php', 'app/Libraries/PluginRepositoryCrawler.php'],
        StatementIndentationFixer:: class => [
            'app/Entities/Person.php',
            'app/Libraries/PluginRepositoryCrawler.php',
        ],
    ]);
