<?php

declare(strict_types=1);

use PhpCsFixer\Fixer\ControlStructure\TrailingCommaInMultilineFixer;
use PhpCsFixer\Fixer\Operator\BinaryOperatorSpacesFixer;
use Symplify\CodingStandard\Fixer\Annotation\RemovePropertyVariableNameDescriptionFixer;
use Symplify\CodingStandard\Fixer\LineLength\LineLengthFixer;
use Symplify\EasyCodingStandard\Config\ECSConfig;

return ECSConfig::configure()
    ->withPaths([__DIR__ . '/app', __DIR__ . '/tests'])
    ->withRootFiles()
    ->withPreparedSets(cleanCode: true, common: true, symplify: true, psr12: true, strict: true)
    ->withSkip([
        RemovePropertyVariableNameDescriptionFixer::class => [__DIR__ . '/app/Views'],
        LineLengthFixer::class                            => [__DIR__ . '/app/Views/*'],
    ])
    ->withConfiguredRule(BinaryOperatorSpacesFixer::class, [
        'operators' => [
            '=>' => 'align_single_space_minimal',
        ],
    ])
    ->withConfiguredRule(TrailingCommaInMultilineFixer::class, [
        'elements' => ['arguments', 'array_destructuring', 'arrays', 'match', 'parameters'],
    ]);
