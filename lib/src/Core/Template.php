<?php

namespace Pranto\Core;

class Template
{
    private $tpl_dir;
    private string $file;
    private array $vars;
    public string $ext = '.html';
    private $app;

    public function __construct(string $template, array $vars, string $tpl_dir = '')
    {
        $this->app = app();
        if ($tpl_dir) {
            $this->tpl_dir = $tpl_dir;
        } else {
            $this->tpl_dir = join_path($this->app->path, $this->app->config['tpl_dir']);
        }

        $this->file = join_path($this->tpl_dir, str_replace('.', DIRECTORY_SEPARATOR, $template) . $this->ext);
        $this->vars = $vars;
    }

    public function render()
    {
        $__fileName = join_path($this->app->path, $this->app->config['cache_dir'], 'templates', md5($this->file) . '.php');

        if (!$this->app->config['debug'] && file_exists($__fileName)) {
            extract($this->vars);
            include $__fileName;
            exit;
        } else {
            file_put_contents($__fileName, $this->compile());
            extract($this->vars);
            include $__fileName;
            exit;
        }
    }

    private function compile()
    {
        $data = file_get_contents($this->file) or throw new \Exception('Template not found!');

        preg_match("#@extends\s*?(.+)#", $data, $layout, PREG_UNMATCHED_AS_NULL);

        $data = $this->changeToPhp($data);

        preg_match_all("#<b-(?P<name>\w+)>(?P<body>.+?)</b-(\w+)>#is", $data, $blocks, PREG_UNMATCHED_AS_NULL);
        
        if ($layout) {
            $layout = file_get_contents(join_path($this->tpl_dir, trim(str_replace('.', DIRECTORY_SEPARATOR, $layout[1])) . $this->ext))
                or throw new \Exception('Layout not found!');

            if ($blocks) {
                foreach ($blocks['name'] as $i => $block) {
                    $layout = preg_replace("#<b-$block>(.*?)</b-$block>#is", trim($blocks['body'][$i]), $layout);
                }
            }

            $data = $this->changeToPhp($layout);
            
            preg_match_all("#<b-(?P<name>\w+)>(?P<body>.*?)</b-(\w+)>#is", $data, $blocks, PREG_UNMATCHED_AS_NULL);
            if ($blocks) {
                foreach ($blocks[0] as $block) {
                    $data = str_replace($block, '', $data);
                }
            }
        }

        // minify
        $data = preg_replace('/\s+/', ' ', $data);
        //$data = str_replace('> <', '><', $data);

        return $data;
    }

    private function changeToPhp(string $data)
    {
        // PHP Codes
        $data = preg_replace("#@(if|elseif|foreach|for|while|switch)\s*?\((.+)\)#", "<?php $1 ($2) : ?>", $data);
        $data = preg_replace('#@end(if|elseif|foreach|for|while|switch)#', "<?php end$1; ?>", $data);
        $data = str_replace('@else', '<?php else : ?>', $data);

        // Echo
        $data = str_replace(['{{', '}}'], ['<?php echo ', '; ?>'], $data);

        // Include
        preg_match_all("#@include\s*?(?P<file>.+)#", $data, $files, PREG_UNMATCHED_AS_NULL);

        if ($files['file']) {
            foreach ($files['file'] as $i => $file) {
                $file = file_get_contents(join_path($this->tpl_dir, trim(str_replace('.', DIRECTORY_SEPARATOR, $file)) . $this->ext))
                    or throw new \Exception('Include file not found!');
                $data = str_replace($files[0][$i], $file, $data);
            }
        }

        return $data;
    }
}