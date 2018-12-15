<?php

namespace AfterBug\TuiEditor;

use AfterBugHQ\AfterBug\Foundation\Modules\BaseModule;
use AfterBugHQ\AfterBug\Support\ComposerWrapper;
use Collective\Html\FormFacade;
use DOMDocument;
use Esemve\Hook\Facades\Hook;
use GrahamCampbell\Markdown\Facades\Markdown;

class Module extends BaseModule
{
    /**
     * Register editor config.
     */
    private function registerEditorConfig()
    {
        $options = json_encode([
            'editor' => config()->get('tuieditor')
        ]);

        $js = <<<EOF
<script>
AfterBug.addOptions($options)
</script>
EOF;

        (new ComposerWrapper([], 'scripts', $js))->compose(['issue.show', 'issue.partials.form']);

        $js = <<<EOF
<script>
AfterBug.addOptions({"editor":{"height":"150px"}})
</script>
EOF;

        (new ComposerWrapper([], 'scripts', $js))->compose(['issue.show']);
    }

    /**
     * Register editor assets.
     */
    private function registerAssets()
    {
        $this->registerEditorConfig();

        $this->addJs([
            __DIR__ . '/Resources/assets/js/editor.js',
            __DIR__ . '/Resources/assets/js/app.js'
        ], ['issue.show', 'issue.partials.form']);

        $this->addCss(
            __DIR__ . '/Resources/assets/css/editor.css',
            ['issue.show', 'issue.partials.form']
        );
    }

    /**
     * Extract editor text area attributes.
     *
     * @param string $html
     * @return array
     */
    private function extractEditorAttributes(string $html)
    {
        $dom = new DOMDocument();
        $dom->loadHTML($html);

        $result = [];
        $element = $dom->getElementsByTagName('textarea')->item(0);

        if ($element && $element->hasAttributes()) {
            foreach ($element->attributes as $attr) {
                $result[$attr->nodeName] = $attr->nodeValue;
            }
        }

        return $result;
    }

    /**
     * Boot method, called right before the request route.
     *
     * @return void
     */
    protected function bootModule()
    {
        Hook::listen('post_content', function ($callback, $output, $variables) {
            return Markdown::convertToHtml(trim($output));
        });

        Hook::listen('template.editor', function ($callback, $output, $variables) {
            $attributes = $this->extractEditorAttributes($output);

            $errors = array_get($variables, 'errors');

            $editorField = array_get($attributes, 'name', 'description');

            $html = FormFacade::textarea($editorField, null, array_merge($attributes, [
                'rows' => 3,
                'class' => 'd-none form-control' . ($errors->has($editorField) ? ' is-invalid' : '')
            ]));

            $html .= '<div id="editor" 
                class="'.($errors->has($editorField) ? ' is-invalid' : '').'"></div>';

            return $html;
        });

        $this->registerAssets();
    }
}
