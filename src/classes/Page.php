<?php

class Page
{
    /** @var string Page id/path */
    private $path = '';

    /**
     * Page constructor.
     * @param $page
     */
    public function __construct($get)
    {
        $this->path = $get['page']??'index';
        $this->isAllowedPage();
    }

    /**
     * Load page
     */
    public function load()
    {
        $controller = new Controller($this);
        $controller->process();
    }

    /**
     * Check if page is allowed for non logged in users
     */
    private function isAllowedPage()
    {
        if(!preg_match("~^\w+$~", $this->path)) {
            die("Page id must be alphanumeric");
        }
        if ((!App::get('session')->isLoggedIn() && $this->path == 'form') || (App::get('session')->isLoggedIn() && ($this->path == 'login' || $this->path == 'register'))) {
            header('Location: /');
            exit();
        }
    }

    /**
     * Get page path
     *
     * @return string
     */
    public function getPath()
    {
        return $this->path;
    }

    public function redirect($url)
    {
        header('Location: ' . $url);
    }
}