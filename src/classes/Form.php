<?php

class Form
{
    /** Validate upload form field values
     *
     * @param $data
     * @return array|bool
     */
    public function validate($data)
    {
        $errors = array();

        if (empty($data['authorname']) || strlen($data['authorname']) > 40) {
            $errors[] = 'Author shouldn\'t be empty or more 40 characters';
        }

        if (empty($data['description']) || strlen($data['description']) > 255) {
            $errors[] = 'Description shouldn\'t be empty or more 255 characters';
        }
        if (empty($_FILES)) {
            $errors[] = 'You should choose file';
        }
        if (!in_array(getimagesize($_FILES['image']['tmp_name'])['mime'], ['image/jpeg', 'image/png', 'image/gif'])) {
            $errors[] = 'File should be JPEG, PNG, GIF';
        }

        if (!empty($errors)) {
            $_SESSION['fields'] = $data;
            $_SESSION['errors'] = $errors;
            return false;
        } else {
            return true;
        }
    }


    /** Validate login form field values
     *
     * @param $data
     * @return array|bool
     */
    public function validateLogin($data)
    {
        $errors = array();

        if (empty($data['login'])) {
            $errors[] = 'Login shouldn\'t be empty';
        }

        if (empty($data['pass'])) {
            $errors[] = 'Password shouldn\'t be empty';
        }


        if (!empty($errors)) {
            $_SESSION['errors'] = $errors;
            $_SESSION['fields'] = $data;
            return false;
        } else {
            return true;
        }
    }

    /** Validate registration form
     *
     * @param $data
     * @return bool
     */
    public function validateRegistration($data)
    {
        $errors = array();

        if (empty($data['login'])) {
            $errors[] = 'Login shouldn\'t be empty';
        }

        if (empty($data['pass']) || empty($data['repass'])) {
            $errors[] = 'Password shouldn\'t be empty';
        }

        if ($data['pass'] != $data['repass']) {
            $errors[] = 'Passwords don\'t match';
        }


        if (!empty($errors)) {
            $_SESSION['errors'] = $errors;
            $_SESSION['fields'] = $data;
            return false;
        } else {
            return true;
        }
    }
}