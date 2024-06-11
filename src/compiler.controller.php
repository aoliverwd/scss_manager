<?php

namespace SCSSWrapper\Controller;

use ScssPhp\ScssPhp\Compiler as SCSSPHPCompiler;
use SCSSWrapper\Model\Compiler as CompilerModel;

/**
 * SCSS Compiler class
 */
class Compiler
{
    private string $compiled_text = '';
    private object $scss_compiler;
    private object $compiler_model;

    /**
     * Constructor
     * @param array<mixed> $options
     */
    public function __construct(array $options = [])
    {
        $cache_options = [];

        foreach (['cacheDir', 'prefix', 'forceRefresh'] as $option_name) {
            if (isset($options[$option_name])) {
                $cache_options[$option_name] = $options[$option_name];
            }
        }

        $this->scss_compiler = new SCSSPHPCompiler($cache_options);
        $this->compiler_model = new CompilerModel($options);
    }

    /**
     * Compile SCSS assets
     * @param  array<string> $scss_asset_locations
     * @param  string $export_file_location
     * @return string|null
     */
    public function compile(array $scss_asset_locations, string $export_file_location): string|null
    {
        // Check if should recompile asset
        if ($this->shouldReCompile($scss_asset_locations)) {
            // Iterate through each asset location
            array_map(function ($asset_location) {
                if (file_exists($asset_location) && method_exists($this->scss_compiler, 'compileFile')) {
                    $result = $this->scss_compiler->compileFile($asset_location);
                    $css = $result->getCss();
                    $this->compiled_text .= is_string($css) ? $css : '';
                }
            }, array_filter($scss_asset_locations));

            // Export to file
            if (!empty($this->compiled_text)) {
                file_put_contents($export_file_location, $this->compiled_text);
            }
        }

        return file_exists($export_file_location) ? $export_file_location : null;
    }

    /**
     * Check if re compile is required
     * @param  array<string>  $scss_asset_locations
     * @return boolean
     */
    private function shouldReCompile(array $scss_asset_locations): bool
    {
        if ($this->compiler_model instanceof CompilerModel) {
            $asset_records = $this->compiler_model->getAssetInfo($scss_asset_locations);
        }

        return true;
    }
}
