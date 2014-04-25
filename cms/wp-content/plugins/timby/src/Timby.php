<?php

class Timby
{
    public function __construct()
    {

    }

    public function TimbyCoreAPIControllerPath($defaultPath)
    {
        return $this->getSourcePath() . 'API/Controller/timby.php';
    }

    public function TimbyPostsAPIControllerPath($defaultPath)
    {
        return $this->getSourcePath() . 'API/Controller/timbyposts.php';
    }

    public function TimbyUsersAPIControllerPath($defaultPath)
    {
        return $this->getSourcePath() . 'API/Controller/timbyusers.php';
    }

    public function addAPIControllers($controllers)
    {
        $controllers[] = 'Timby';
        $controllers[] = 'TimbyPosts';
        $controllers[] = 'TimbyUsers';

        return $controllers;
    }

    protected function getSourcePath()
    {
        return plugin_dir_path(__FILE__);
    }
}
