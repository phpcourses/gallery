<?php

error_reporting(E_ALL | E_STRICT);
session_start();

spl_autoload_register(function ($classname) {
    require_once('src/classes/' . $classname . '.php');
});

/** image qty on page */
define('IMAGE_COUNT', 9);
/** defined constant that consists page title  */
define('PAGE_TITLE', 'Image Gallery');
/** defined image placeholder  */
define('IMAGE_PLACEHOLDER', 'https://fakeimg.pl/300x200/282828/eae0d0/?retina=1');
/** defined constant with path to images stored folder */
define('IMAGE_RESOURCE_URL', 'pub/media/images/');
/** defined constant with path to images stored folder */
define('IMAGE_THUMBNAIL_URL', 'pub/media/thumbnails/');
/** defined constant with path to csv files */
define('DATA_PATH', 'pub/media/data/');
/** text file with users list */
define('USERS_FILE', 'var/users.txt');
/** text file with users list */
define('ERROR_LOG', 'var/error.log');
/** project config file */
define('CONFIG_FILE', 'var/config.ini');

/** Sort array of images
 * @param $images
 */
function sortImages(&$images)
{
    if (!empty($images)) {
        //sorting part
        usort($images, function ($imageA, $imageB) {
            if ($imageA['created_at'] == $imageB['created_at']) {
                return 0;
            }
            return ($imageA['created_at'] < $imageB['created_at']) ? -1 : 1;
        });
    }
}

/** Get formatted current time
 *
 * @return false|string
 */
function getCurrentDate()
{
    return date('d M Y H:i:s', time());
}

/**
 * Return image page or placeholder
 *
 * @param $imagePath
 * @return string
 */
function imageExists($imagePath)
{
    if (file_exists(IMAGE_RESOURCE_URL . $imagePath)) {
        return IMAGE_RESOURCE_URL . $imagePath;
    } else {
        return IMAGE_PLACEHOLDER;
    }
}

/** Generate image thumbnail
 *
 * @param $imagePath
 * @param $width
 * @param $height
 * @return bool|string
 * @throws Exception
 */
function generateThumbnail($imagePath, &$width, &$height)
{
    if (!createDir(IMAGE_THUMBNAIL_URL)) {
        return IMAGE_PLACEHOLDER;
    }

    $params = getOriginalSize($imagePath);
    $thumbnailPath = resizeImage($imagePath, $width, $height, $params);
    list($width, $height) = $params;
    if ($thumbnailPath) {
        return $thumbnailPath;
    } else {
        return IMAGE_PLACEHOLDER;
    }
}

/** Resize Image
 *
 * @param $imagePath
 * @param $width
 * @param $height
 * @param $params
 * @return bool|string
 * @throws Exception
 */
function resizeImage($imagePath, $width, $height, $params)
{
    $filename = IMAGE_THUMBNAIL_URL . basename($imagePath);
    if (file_exists($filename)) {
        return $filename;
    }

    $mime = $params['mime'];

    //use specific function based on image format
    switch ($mime) {
        case 'image/jpeg':
            $imageCreateFunc = 'imagecreatefromjpeg';
            $imageSaveFunc = 'imagejpeg';
            break;

        case 'image/png':
            $imageCreateFunc = 'imagecreatefrompng';
            $imageSaveFunc = 'imagepng';
            break;

        case 'image/gif':
            $imageCreateFunc = 'imagecreatefromgif';
            $imageSaveFunc = 'imagegif';
            break;

        default:
            throw new Exception('this file format isn\'t supported');
    }

    //Variable function
    $img = $imageCreateFunc($imagePath);

    //list is php construction that allows to set array elements to variables
    list($originalWidth, $originalHeight) = $params;

    //calculate height
    if (!$height) {
        $height = ($originalHeight / $originalWidth) * $width;
    }
    //create new image
    $bufferImage = imagecreatetruecolor($width, $height);
    imagecopyresampled($bufferImage, $img, 0, 0, 0, 0, $width, $height, $originalWidth, $originalHeight);

    //return buffer output as string
    ob_start();
    $imageSaveFunc($bufferImage);
    $imageSource = ob_get_clean();

    if (file_put_contents($filename, $imageSource)) {
        return $filename;
    }

    return false;

}

/** get image size info
 *
 * @param $imagePath
 * @return array|bool
 */
function getOriginalSize($imagePath)
{
    return getimagesize($imagePath);
}

/** Return array with images
 *
 * @return mixed
 */
function getCollection()
{
    $database = connect();
    $offset = isset($_GET['p']) ? $_GET['p'] - 1 : 0;
    $offset = $offset * IMAGE_COUNT;
    $sql = "SELECT images.id, image_path, thumbnail_path, author_name, description, created_at, login FROM images
LEFT JOIN users on images.user_id = users.id
LIMIT " . $offset . ", " . IMAGE_COUNT;


    $result = request($database, $sql);
    $images = [];
    if ($result->rowCount() > 0) {
        foreach ($result->fetchAll() as $value) {
            $images[] = $value;
        }
    } else {
        // set empty array
        $images = [];
    }
    sortImages($images);

    return $images;
}

/** Return qty of pages
 *
 * @return float|int
 */
function getPageCount()
{
    $database = connect();
    $result = request($database, 'SELECT COUNT(id) FROM images');
    return ceil($result->fetchColumn(0) / IMAGE_COUNT);
}

/** Get last page number
 *
 * @return int
 */
function getLastPage(): int
{
    return getPageCount();
}

/** Get first page, first page is 1
 *
 * @return int
 */
function getFirstPage()
{
    return 1;
}

/** Get next page number
 *
 * @return bool|int
 */
function getNextPage()
{
    if (isset($_REQUEST['p']) && getPageCount() <= $_REQUEST['p']) {
        return false;
    } elseif (isset($_REQUEST['p'])) {
        return $_REQUEST['p'] + 1;
    } else {
        return 2;
    }
}

/** Get previous page number
 *
 * @return bool|int
 */
function getPrevPage()
{
    return isset($_REQUEST['p']) && $_REQUEST['p'] > 1 ? $_REQUEST['p'] - 1 : false;
}

/** Get current page number
 *
 * @return int
 */
function getCurrentPage()
{
    return isset($_REQUEST['p']) ? $_REQUEST['p'] : 1;
}

/** Generate pagination HTML
 *
 * @return string
 */
function renderPagination()
{
    $paginationHtml = '';
    if (getPageCount() > 1) {
        $paginationHtml .= "<li class='page-item'><a class='page-link' href='/?p=" . getFirstPage() . "'>Go to first page</a></li>";
        if ($prevPage = getPrevPage()) {
            $paginationHtml .= "<li class='page-item'><a class='page-link' href='/?p=" . $prevPage . "'>" . $prevPage . "</a></li>";
        }
        $paginationHtml .= "<li class='page-item active'><a class='page-link' href='#'>" . getCurrentPage() . "</a></li>";
        if ($nextPage = getNextPage()) {
            $paginationHtml .= "<li class='page-item'><a class='page-link' href='/?p=" . $nextPage . "'>" . $nextPage . "</a></li>";
        }
        $paginationHtml .= "<li class='page-item'><a class='page-link' href='/?p=" . getLastPage() . "'>Go to last page</a></li>";
    }

    return $paginationHtml;
}

/** Get errors from request
 *
 * @return bool|string
 */
function getErrors()
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

/** Validate upload form field values
 *
 * @param $data
 * @return array|bool
 */
function validateUpload($data)
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

/** Processing data from form
 *
 * @param $data
 */
function process(&$data)
{
    if (is_array($data)) {
        foreach ($data as &$value) {
            addslashes($value);
        }
    }
}

/** Move file into destination directory, check directory existing
 *
 * @param $file
 * @return bool|string
 */
function uploadFile($file)
{
    if (!createDir(IMAGE_RESOURCE_URL)) {
        return false;
    }

    $filename = IMAGE_RESOURCE_URL . time() . $file['name'];
    if (move_uploaded_file($file['tmp_name'], $filename)) {
        return $filename;
    }

    return false;
}

/** save everything including file, form data and generate thumbnail
 * @return bool
 * @throws Exception
 */
function save()
{
    $database = connect();
    $sql = 'INSERT INTO images(id, image_path, thumbnail_path, description, author_name, created_at, user_id)
VALUES(NULL, :image_path, :thumbnail_path, :description, :author_name, CURRENT_TIMESTAMP(), :user_id)';
    if ($filename = uploadFile($_FILES['image'])) {
        $width = 348;
        $height = 0;
        $params = [
            ':image_path' => $filename,
            ':thumbnail_path' => generateThumbnail($filename, $width, $height),
            ':description' => $_REQUEST['description'],
            ':author_name' => $_REQUEST['authorname'],
            ':user_id' => $_SESSION['auth'],
        ];
        request($database, $sql, $params);

        $_SESSION['messages'] = ['You have uploaded new image'];
        unset($_SESSION['fields']);

        return true;
    }
    $_SESSION['errors'][] = 'Unable to upload image';

    return false;
}

/** Get data from CSV file
 *
 * @param $filename
 * @return array|bool|false|null
 */
function getData($filename)
{
    if (file_exists(DATA_PATH . basename($filename) . '.csv')) {
        $handle = fopen(DATA_PATH . basename($filename) . '.csv', 'r');
        if ($handle) {
            return fgetcsv($handle);
        }
        fclose($handle);
    }

    return false;
}

/** Check directory existing and create it if not
 *
 * @param $path
 * @return bool
 */
function createDir($path)
{
    if (!file_exists($path)) {
        return mkdir($path, 0777);
    }
    return true;
}

/** Check if user is logged in
 *
 * @return bool
 */
function isLoggedIn()
{
    if (isset($_SESSION['auth']) && !empty($_SESSION['auth'])) {
        return true;
    }

    return false;
}

/** Authorize user
 *
 * @param $postUser
 * @param $postPass
 * @return bool
 */
function authUser($postUser, $postPass)
{
    $database = connect();
    $pass = crypt($postPass, $postUser);
    $result = request($database, 'SELECT id FROM users WHERE login = :login AND password = :pass', [':login' => $postUser, ':pass' => $pass]);
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

/** Validate login form field values
 *
 * @param $data
 * @return array|bool
 */
function validateLogin($data)
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

/** Get filed value from session
 *
 * @param $field
 * @return string
 */
function getFieldValue($field)
{
    if (isset($_SESSION['fields'][$field])) {
        return $_SESSION['fields'][$field];
    }

    return '';
}

/** Get messages from session
 *
 * @return bool|string
 */
function getMessages()
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

/** Unset auth session
 *
 */
function logOut()
{
    unset($_SESSION['auth']);
    $_SESSION['messages'] = ['You have logged out'];
    header('Location: /');
}

/** Remove image
 *
 * @param $id
 * @return bool
 */
function deleteImage($id)
{
    if (isLoggedIn()) {
        $database = connect();
        $image = request($database, "SELECT image_path, thumbnail_path FROM images WHERE id = :id", [':id' => $id]);
        request($database, "DELETE FROM images WHERE id = :id", [':id' => $id]);
        unlink($image->fetchColumn(0));
        unlink($image->fetchColumn(1));
        $_SESSION['messages'] = ['You have deleted image'];
        return true;
    } else {
        $_SESSION['errors'] = ['You haven\'t permitted to delete images'];
        return false;
    }
}

/** Validate registration form
 *
 * @param $data
 * @return bool
 */
function validateRegistration($data)
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

/** add user info into file and auth user
 *
 * @param $login
 * @param $pass
 * @return bool
 */
function createUser($login, $pass)
{
    $pass = crypt($pass, $login);
    $database = connect();
    if (request($database, 'INSERT INTO users(id, login, password) VALUES(NULL, :login, :pass)', array(':login' => $login, ':pass' => $pass))) {
        $_SESSION['auth'] = $database->lastInsertId();
        $_SESSION['messages'][] = 'Your account has been created';
        return true;
    }
    $_SESSION['errors'][] = 'Something went wrong';
    return false;
}

/** Create connection to database
 * @return PDO
 */
function connect()
{
    try {
        if (file_exists(CONFIG_FILE)) {
            $config = parse_ini_file(CONFIG_FILE);
            return new PDO($config['engine'] . ':host=' . $config['host'] . ';dbname=' . $config['dbname'], $config['user'], $config['path']);
        } else {
            throw new Exception('Config file is not exist');
        }
    } catch (PDOException $exception) {
        error_log($exception->getMessage(), 3, $_SERVER['DOCUMENT_ROOT'] . ERROR_LOG);
        echo 'Could not connect to DB';
        exit;
    } catch (Exception $exception) {
        error_log($exception->getMessage(), 3, $_SERVER['DOCUMENT_ROOT'] . ERROR_LOG);
        echo 'Could not connect to DB';
        exit;
    }
}

/** Process database queries
 *
 * @param PDO $database
 * @param string $sql
 * @param array $params
 * @return bool|int|PDOStatement
 */
function request(PDO $database, $sql, $params = [])
{
    $query = $database->prepare($sql);
    $result = $query->execute($params);
    if ($result === false) {
        error_log($query->errorInfo()[2], 3, $_SERVER['DOCUMENT_ROOT'] . ERROR_LOG);
        return false;
    }

    return $query;
}