<?php

class User extends DataEntity
{
    /** add user info into file and auth user
     *
     * @param $login
     * @param $pass
     * @return bool
     */
    public function create($login, $pass)
    {
        $pass = crypt($pass, $login);
        if ($this->request('INSERT INTO users(id, login, password) VALUES(NULL, :login, :pass)', array(':login' => $login, ':pass' => $pass))) {
            $_SESSION['auth'] = $this->database->lastInsertId();
            $_SESSION['messages'][] = 'Your account has been created';
            return true;
        }
        $_SESSION['errors'][] = 'Something went wrong';
        return false;
    }

    public function logout()
    {
        App::get('session')->clear();
    }

    public function save()
    {

    }

    /** Authorize user
     *
     * @param $postUser
     * @param $postPass
     * @return bool
     */
    function auth($postUser, $postPass)
    {
        $pass = crypt($postPass, $postUser);
        $result = $this->request('SELECT id FROM users WHERE login = :login AND password = :pass', [':login' => $postUser, ':pass' => $pass]);
        if ($result->rowCount() == 1) {
            $_SESSION['auth'] = $result->fetchColumn(0);
            $_SESSION['messages'] = ['You have logged in successfuly'];
            unset($_SESSION['fields']);
            return true;
        }

        $_SESSION['errors'] = ['Incorrect Login'];
        $_SESSION['fields'] = $_POST;

        return false;
    }
}