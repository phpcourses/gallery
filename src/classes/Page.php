<?php

class Page
{
    public $page = '';

    function __construct($page)
    {
        $this->page = $page??'index';
        $this->isAllowedPage();
    }

    function render($page = 'index')
    {
        require_once('view/' . $page . '.php');
    }

    function process($page)
    {
        require_once('src/' . $page . '.php');
    }

    function load()
    {
        if (file_exists('view/' . $this->page)) {
            $this->render($this->page);
        } elseif (file_exists('src/' . $this->page)) {
            $this->process($this->page);
        } else {
            $this->render();
        }
    }

    /** Check if page is allowed for non logged in users
     */
    function isAllowedPage()
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