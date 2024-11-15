<?php

namespace SCSSWrapper\Controllers;

use ScssPhp\ScssPhp\Compiler as SCSSPHPCompiler;
use SCSSWrapper\Models\Compiler as CompilerModel;

/**
 * SCSS Compiler class
 */
class Compiler
{
    private string $compiled_blob = '';
    public SCSSPHPCompiler $scss_compiler;
    private CompilerModel $compiler_model;
    private bool $forceRefresh;
    private bool $addCtime;

    /**
     * Constructor
     * @param array<mixed> $options
     */
    public function __construct(array $options = [])
    {
        $cache_options = [];

        foreach (['cacheDir', 'prefix'] as $option_name) {
            if (isset($options[$option_name])) {
                $cache_options[$option_name] = $options[$option_name];
            }
        }

        $this->scss_compiler = new SCSSPHPCompiler($cache_options);
        $this->compiler_model = new CompilerModel($options);
        $this->forceRefresh = isset($options['forceRefresh']) ? boolval($options['forceRefresh']) : false;
        $this->addCtime = isset($options['addCtime']) ? boolval($options['addCtime']) : false;
    }

    /**
     * Compile SCSS assets
     * @param  array<string> $scss_asset_locations
     * @param  string $export_file_location [Can be file or folder location]
     * @return string|null
     */
    public function compile(array $scss_asset_locations, string $export_file_location): string|null
    {
        // Check if should recompile asset
        if ($this->shouldReCompile($scss_asset_locations, $export_file_location)) {
            // Iterate through each asset location
            array_map(function ($asset_location) {
                if (file_exists($asset_location) && method_exists($this->scss_compiler, 'compileFile')) {
                    $result = $this->scss_compiler->compileFile($asset_location);
                    $css = $result->getCss();
                    $this->compiled_blob .= is_string($css) ? $css : '';
                }
            }, array_filter($scss_asset_locations));

            // Export to file
            if (!empty($this->compiled_blob)) {
                // Should generate an export filename
                try {
                    if (is_dir($export_file_location)) {
                        $filename = hash('crc32b', $this->compiled_blob) . '.css';
                        $export_file_location .= (substr($export_file_location, -1) !== '/' ? '/' : '') . $filename;
                    }
                } catch (\Exception $e) {
                    exit($e->getMessage());
                }

                file_put_contents($export_file_location, $this->compiled_blob);
                return $export_file_location . ($this->addCtime ? '?ctime=' . filectime($export_file_location) : '');
            }
        }

        return file_exists($export_file_location) ? $export_file_location . ($this->addCtime ? '?ctime=' . filectime($export_file_location) : '') : null;
    }

    /**
     * Check if re compile is required
     * @param  array<string>  $scss_asset_locations
     * @param  string $export_file_location
     * @return boolean
     */
    private function shouldReCompile(array $scss_asset_locations, string $export_file_location): bool
    {
        if ($this->forceRefresh) {
            return true;
        }

        if (is_dir($export_file_location) || !file_exists($export_file_location)) {
            return true;
        }

        if ($this->compiler_model instanceof CompilerModel) {
            $asset_records = $this->compiler_model->getAssetInfo($scss_asset_locations);
            if (!empty($asset_records)) {
                foreach ($asset_records as $asset) {
                    if ($asset['updated']) {
                        return true;
                    }
                }
            }
        }

        return false;
    }
}
