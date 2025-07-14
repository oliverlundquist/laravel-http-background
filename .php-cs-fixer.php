<?php

$finder = PhpCsFixer\Finder::create()
            ->in(['src', 'tests']);

return (new PhpCsFixer\Config)
            ->setUsingCache(true)
            ->setRiskyAllowed(true)
            ->setUnsupportedPhpVersionAllowed(true)
            ->setRules([
                '@PSR2'                           => true,
                'array_syntax'                    => ['syntax' => 'short'], // PHP arrays should use the PHP 5.4 short-syntax.
                'ordered_imports'                 => true, // Ordering use statements.
                'concat_space'                    => ['spacing' => 'one'], // Concatenation should be used with at least one whitespace around.
                // 'binary_operator_spaces'       => ['operators' => ['=' => 'align', '=>' => 'align']], // Align equals and double arrow vertically.
                'no_whitespace_in_blank_line'     => true, // Remove trailing whitespace at the end of blank lines.
                'no_unused_imports'               => true, // Unused use statements must be removed.
                'single_blank_line_at_eof'        => true, // A file must always end with a single empty line feed.
                'declare_strict_types'            => true, // Force strict types declaration in all files.
                'blank_lines_before_namespace'    => true,
                'strict_comparison'               => true, // Comparisons should be strict.
                'phpdoc_align'                    => true, // Phpdoc tags must be aligned vertically.
                'statement_indentation'           => false,
                'no_multiple_statements_per_line' => false
            ])
            ->setFinder($finder);
