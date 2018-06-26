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
        $pageAction = $this->page->getPath() . "Action";
        if (method_exists($this, $pageAction)) {
            $this->$pageAction();
        } else {
            $this->notFoundAction();
        }
    }

    /**
     * render index page
     */
    private function indexAction()
    {
        $this->_render('view/index.php', [
            'title' => 'Image Gallery',
            'image' => new Image(),
            'pagination' => new Pagination(),
        ]);
    }

    /**
     * render image upload page
     */
    private function formAction()
    {
        $this->_render('view/form.php', [
            'errors' => App::get('session')->messages()
        ]);
    }

    /**
     * Render user login page
     */
    private function loginAction()
    {
        $this->_render('view/login.php', [
            'errors' => App::get('session')->messages()
        ]);
    }

    /**
     * Render register page
     */
    private function registerAction()
    {
        $this->_render('view/register.php', [
            'errors' => App::get('session')->messages()
        ]);
    }

    /**
     * validate and save image
     *
     * @throws Exception
     */
    private function processAction()
    {
        $request = $_REQUEST;

        if (($valid = App::get('form')->validate($request)) === true) {
            if (App::get('image')->save()) {
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

        if (App::get('form')->validateLogin($post) && App::get('user')->auth($post['login'], $post['pass'])) {
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
        App::get('user')->logout();
        App::get('session')->setMessage('You have been logged out');
        $this->page->redirect('/');
    }
    /**
     * Registration process
     */
    private function processRegisterAction()
    {
        $post = $_POST;

        if (validateRegistration($post) && createUser($post['login'], $post['pass'])) {
            header('Location: /');
        } else {
            header('Location: /register');
        }
    }

    /**
     * Remove image
     */
    private function removeImageAction()
    {
        $id = $_REQUEST['id'];
        App::get('image')->delete($id);
        $this->page->redirect('/');
    }

    private function notFoundAction()
    {
        $this->_render('view/404.php');
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