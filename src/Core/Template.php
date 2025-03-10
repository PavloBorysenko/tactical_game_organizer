<?php

declare(strict_types=1);

namespace TacticalGameOrganizer\Core;

use function esc_html__;
use function esc_html_e;
use function esc_attr;
use function esc_html;
use function selected;

/**
 * Base Template class
 * 
 * Provides common functionality for template handling
 * 
 * @package TacticalGameOrganizer\Core
 */
class Template {
    /**
     * Template directory
     *
     * @var string
     */
    protected const TEMPLATE_DIR = TGO_PLUGIN_DIR . 'src/templates/';

    /**
     * Load and render a template file
     *
     * @param string $template Template file path relative to TEMPLATE_DIR
     * @param array $data Data to be extracted and available in the template
     * @return void
     */
    protected function renderTemplate(string $template, array $data = []): void {
        $template_path = self::TEMPLATE_DIR . $template;

        if (!file_exists($template_path)) {
            error_log(sprintf('Template file not found: %s', $template_path));
            return;
        }

        if (!empty($data)) {
            extract($data);
        }

        include $template_path;
    }

    /**
     * Get the full path to a template file
     *
     * @param string $template Template file path relative to TEMPLATE_DIR
     * @return string Full path to the template file
     */
    protected function getTemplatePath(string $template): string {
        return self::TEMPLATE_DIR . $template;
    }
} 