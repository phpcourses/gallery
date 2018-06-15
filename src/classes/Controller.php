<?php

class Controller
{
    /** @var Page  */
    public $page;

    /**
     * Controller constructor.
     * @param Page $page
     */
    public function __construct(Page $page)
    {
        $this->page = $page;
    }

    /**
     * This calls other methods based on page GET requests
     */
    public function process()
    {
        $this->{$this->page->getPath() . "Action"}();
    }

    /**
     * render index page
     */
    private function indexAction()
    {
        $this->_render('view/index.php', []);
    }

    /**
     * render image upload page
     */
    private function formAction()
    {
        $this->_render('view/form.php', []);
    }

    /**
     * Render user login page
     */
    private function loginAction()
    {
        $this->_render('view/login.php', []);
    }

    /**
     * Render register page
     */
    private function registerAction()
    {
        $this->_render('view/register.php', []);
    }

    /**
     * validate and save image
     *
     * @throws Exception
     */
    private function processAction()
    {
        $request = $_REQUEST;

        if (($valid = validateUpload($request)) === true) {
            if (save()) {
                header('Location: /');
            } else {
                header('Location: /form');
            }
        } else {
            header('Location: /');
        }
    }

    /**
     * authorization process
     */
    private function processLoginAction()
    {
        $post = $_POST;

        if (validateLogin($post) && authUser($post['login'], $post['pass'])) {
            header('Location: /');
        } else {
            header('Location: /login');
        }
    }

    /**
     * Logout user
     */
    private function logoutAction()
    {
        logOut();
    }
    /**
     * Registration process
     */
    private function processRegisterAction()
    {
        $post = $_POST;

        if (validateRegistration($post) && createUser($post['login'], $post['pass'], $post['repass'])) {
            header('Location: /');
        } else {
            header('Location: /register');
        }
    }

    /**
     * Remove image
     */
    public function removeImageAction()
    {
        $id = $_REQUEST['id'];

        deleteImage($id);

        header('Location: /');
    }

    /**
     * Extract data from array to variables and pass them to included template
     * @param $template
     * @param array $params
     */
    private function _render($template, $params = [])
    {
        extract($params);
        include($template);
    }
}