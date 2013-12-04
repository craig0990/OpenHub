<?php

namespace OpenHub\Web;

class AbstractPage extends \Aura\Web\Controller\AbstractPage {

    public function template($template, $data) {
        $m = new \Mustache_Engine;
        return $m->render(
            file_get_contents('templates/' . $template . '.mustache'),
            $data
        );
    }

}
