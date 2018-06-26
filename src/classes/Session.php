<?php

class Session
{
    public function __construct()
    {
        session_start();
    }

    /** Get messages from session
     *
     * @return bool|string
     */
    public function getMessages()
    {
        if (isset($_SESSION['messages']) && !empty($_SESSION['messages'])) {
            $messages = '';
            foreach ($_SESSION['messages'] as $message) {
                $messages .= $message . '<br>';
            }
            unset($_SESSION['messages']);
            return $messages;
        }

        return false;
    }

    /** Get errors from request
     *
     * @return bool|string
     */
    public function getErrors()
    {
        if (isset($_SESSION['errors']) && !empty($_SESSION['errors'])) {
            $errors = '';
            foreach ($_SESSION['errors'] as $error) {
                $errors .= $error . '<br>';
            }
            unset($_SESSION['errors']);
            return $errors;
        }

        return false;
    }

    /**
     * unset session variable
     */
    public function clear()
    {
        $_SESSION = [];
    }

    public function setMessage($message)
    {
        $_SESSION['messages'][] = $message;
    }

    public function setError($error)
    {
        $_SESSION['messages'][] = $error;
    }

    public function messages()
    {
        return $this->getMessages() . $this->getErrors();
    }

    public function isLoggedIn()
    {
        if (isset($_SESSION['auth']) && !empty($_SESSION['auth'])) {
            return true;
        }

        return false;
    }

    public function getFieldValue($field)
    {
        if (isset($_SESSION['fields'][$field])) {
            return $_SESSION['fields'][$field];
        }

        return '';
    }
}