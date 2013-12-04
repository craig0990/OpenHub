<?php
namespace OpenHub\Web;

class Repository extends AbstractPage
{

    public function actionRoot($repositories, $name, $path) {
        $path = array_values(array_filter($path));
        $dir = getcwd() . DIRECTORY_SEPARATOR . $repositories . DIRECTORY_SEPARATOR . $name . '.git' . DIRECTORY_SEPARATOR;
        $repo = new \Granite\Git\Repository($dir);
        $head = $repo->head();
        $tree = $head->tree();
        $nodes = array_values($tree->nodes());
        foreach ($path as $segment) {
            foreach ($nodes as $i => $node) {
                if ($node->name() === $segment) {
                    if ($node->isDirectory()) {
                        $tree = new \Granite\Git\Tree($dir, $node->sha());
                        $nodes = array_values($tree->nodes());
                        if ($i == count($path)) {
                            return $this->displayDirectory($name, $path, $nodes);
                        }
                    } else {
                        $blob = new \Granite\Git\Blob($dir, $node->sha());
                        return $this->displayFile($name, $path, $blob);
                    }
                }
            }
        }

        return $this->displayDirectory($name, $path, $nodes);
    }

    protected function displayDirectory($name, $path, $nodes) {
        $up = empty($path) ? null : '/' . implode('/', array_slice($path, 0, -1));
        $html = $this->template('repo/directory', array(
            'repo' => $name,
            'up' => $up,
            'path' => implode('/', $path),
            'nodes' => array_map(function($node) {
                return array(
                    'name' => $node->name()
                );
            }, $nodes)
        ));

        $this->response->setContent($this->template('main', array(
            'content' => $html
        )));
    }

    protected function displayFile($name, $path, $blob) {
        $html = '<pre>' . $blob->content() . '</pre>';
        $html = $this->template('repo/file', array(
            'repo' => $name,
            'filename' => implode('/', $path),
            'content' => $blob->content()
        ));
        $this->response->setContent($this->template('main', array(
            'content' => $html
        )));
    }

}
