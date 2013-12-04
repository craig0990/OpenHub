<?php
namespace OpenHub\Web;

class Dashboard extends AbstractPage
{

    public function actionRoot($repositories) {
        $repos = glob($repositories . DIRECTORY_SEPARATOR . '*');

        $html = $this->template('repositories', array(
            'repositories' => array_map(function($repo) {
                return array(
                    'name' => basename($repo, '.git')
                );
            }, $repos))
        );

        $this->response->setContent($this->template('main', array(
            'content' => $html
        )));
    }

}
