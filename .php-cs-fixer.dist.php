<?php

declare(strict_types=1);

require __DIR__ . '/vendor/autoload.php';

$finder = PhpCsFixer\Finder::create()
    ->in([
        __DIR__ . '/config/',
        __DIR__ . '/app/',
        __DIR__ . '/bootstrap/',
        __DIR__ . '/tests/',
    ])
    ->exclude(['cache'])
    ->append([
        __DIR__ . '/.php-cs-fixer.dist.php',
        __DIR__ . '/rector.php',
        __DIR__ . '/phpstan.bootstrap.php',
    ]);

return (new PhpCsFixer\Config())
    ->setFinder($finder)
    ->setRules([
        '@PSR12' => true,
        '@PHP82Migration' => true,
        'blank_line_after_opening_tag' => true,
        'braces_position' => [
            'allow_single_line_empty_anonymous_classes' => true,
        ],
        'control_structure_continuation_position' => true,
        'statement_indentation' => true,
        'no_multiple_statements_per_line' => true,
        'compact_nullable_typehint' => true,
        'declare_equal_normalize' => true,
        'declare_strict_types' => true,
        'lowercase_cast' => true,
        'lowercase_static_reference' => true,
        'new_with_braces' => true,
        'no_blank_lines_after_class_opening' => true,
        'no_leading_import_slash' => true,
        'no_whitespace_in_blank_line' => true,
        'ordered_class_elements' => [
            'order' => [
                'use_trait',
            ],
        ],
        'ordered_imports' => [
            'imports_order' => [
                'class',
                'function',
                'const',
            ],
            'sort_algorithm' => 'alpha',
        ],
        'return_type_declaration' => true,
        'short_scalar_cast' => true,
        'single_trait_insert_per_statement' => true,
        'ternary_operator_spaces' => true,
        'modifier_keywords' => [
            'elements' => [
                'const',
                'method',
                'property',
            ],
        ],
        'array_syntax' => [
            'syntax' => 'short',
        ],
        'binary_operator_spaces' => true,
        'cast_spaces' => true,
        'concat_space' => [
            'spacing' => 'one',
        ],
        'fully_qualified_strict_types' => true,
        'method_argument_space' => true,
        'native_function_invocation' => [
            'include' => [],
            'strict' => true,
        ],
        'single_line_comment_style' => true,
        'single_quote' => true,
        'space_after_semicolon' => true,
        'trailing_comma_in_multiline' => true,
        'trim_array_spaces' => true,
        'types_spaces' => true,
        'unary_operator_spaces' => true,
        'whitespace_after_comma_in_array' => true,
        // Cleanup
        'class_attributes_separation' => [
            'elements' => [
                'const' => 'one',
                'method' => 'one',
                'property' => 'one',
            ],
        ],
        'combine_consecutive_unsets' => true,
        'no_empty_statement' => true,
        'no_superfluous_elseif' => true,
        'no_unused_imports' => true,
        'no_useless_else' => true,
        'no_useless_return' => true,
        // PHPDoc
        'align_multiline_comment' => true,
        'no_empty_phpdoc' => true,
        'no_superfluous_phpdoc_tags' => [
            'allow_mixed' => true,
            'remove_inheritdoc' => true,
        ],
        'phpdoc_align' => [
            'align' => 'left',
        ],
        'phpdoc_no_useless_inheritdoc' => true,
        'phpdoc_order' => true,
        'phpdoc_scalar' => true,
        'phpdoc_separation' => true,
        'phpdoc_trim' => true,
        'phpdoc_types_order' => [
            'null_adjustment' => 'always_last',
        ],
    ])
    ->setRiskyAllowed(true);
