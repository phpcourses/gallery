<?php

class Page
{
    /** @var string Page id/path */
    public $page = '';

    /**
     * Page constructor.
     * @param $page
     */
    public function __construct($page)
    {
        $this->page = $page??'index';
        $this->isAllowedPage();
    }

    /**
     * Render HTML
     * @param string $page
     */
    private function render($page = 'index')
    {
        require_once('view/' . $page . '.php');
    }

    /**
     * Process page
     * @param $page
     */
    private function process($page)
    {
        require_once('src/' . $page . '.php');
    }

    /**
     * Load page
     */
    public function load()
    {
        if (file_exists('view/' . $this->page . '.php')) {
            $this->render($this->page);
        } elseif (file_exists('src/' . $this->page . '.php')) {
            $this->process($this->page);
        } else {
            $this->render();
        }
    }

    /**
     * Check if page is allowed for non logged in users
     */
    private function isAllowedPage()
    {
        if(!preg_match("~^\w+$~", $this->page)) {
            die("Page id must be alphanumeric");
        }
        if ((!isLoggedIn() && $this->page == 'form') || (isLoggedIn() && ($this->page == 'login' || $this->page == 'register'))) {
            header('Location: /');
            exit();
        }
    }
}