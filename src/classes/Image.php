<?php


class Image extends DataEntity
{
    /** @var string defined image placeholder */
    const IMAGE_PLACEHOLDER = 'https://fakeimg.pl/300x200/282828/eae0d0/?retina=1';
    /** @var string defined constant with path to images stored folder */
    const IMAGE_THUMBNAIL_URL = 'pub/media/thumbnails/';
    /** @var string defined constant with path to images stored folder */
    const IMAGE_RESOURCE_URL = 'pub/media/images/';

    /** Sort array of images
     * @param $images
     */
    public function sort(&$images)
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
    public function getCurrentDate()
    {
        return date('d M Y H:i:s', time());
    }

    /**
     * Return image page or placeholder
     *
     * @param $imagePath
     * @return string
     */
    public function exists($imagePath)
    {
        if (file_exists(self::IMAGE_RESOURCE_URL . $imagePath)) {
            return self::IMAGE_RESOURCE_URL . $imagePath;
        } else {
            return self::IMAGE_PLACEHOLDER;
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
    public function thumbnail($imagePath, &$width, &$height)
    {
        if (!$this->createDir(IMAGE_THUMBNAIL_URL)) {
            return IMAGE_PLACEHOLDER;
        }

        $params = $this->getOriginalSize($imagePath);
        $thumbnailPath = $this->resize($imagePath, $width, $height, $params);
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
    public function resize($imagePath, $width, $height, $params)
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
    public function getOriginalSize($imagePath)
    {
        return getimagesize($imagePath);
    }

    /** Return array with images
     *
     * @return mixed
     */
    public function getCollection()
    {
        if (isset($_GET['p'])) {
            $offset = $_GET['p'] - 1;
        } else {
            $offset = 0;
        }
        //$offset = isset($_GET['p']) ? $_GET['p'] - 1 : 0;
        $offset = $offset * Pagination::IMAGE_COUNT;
        $sql = "SELECT images.id, image_path, thumbnail_path, author_name, description, created_at, login FROM images
LEFT JOIN users on images.user_id = users.id
LIMIT " . $offset . ", " . Pagination::IMAGE_COUNT;


        $result = $this->request($sql);
        $images = [];
        if ($result->rowCount() > 0) {
            foreach ($result->fetchAll() as $value) {
                $images[] = $value;
            }
        } else {
            // set empty array
            $images = [];
        }
        $this->sort($images);

        return $images;
    }


    /** Remove image
     *
     * @param $id
     * @return bool
     */
    public function delete($id)
    {
        if (App::get('session')->isLoggedIn()) {
            $image = $this->request("SELECT image_path, thumbnail_path FROM images WHERE id = :id", [':id' => $id]);
            $this->request("DELETE FROM images WHERE id = :id", [':id' => $id]);
            unlink($image->fetchColumn(0));
            unlink($image->fetchColumn(1));
            $_SESSION['messages'] = ['You have deleted image'];
            return true;
        } else {
            $_SESSION['errors'] = ['You haven\'t permitted to delete images'];
            return false;
        }
    }


    /** save everything including file, form data and generate thumbnail
     * @return bool
     * @throws Exception
     */
    public function save()
    {
        $sql = 'INSERT INTO images(id, image_path, thumbnail_path, description, author_name, created_at, user_id)
VALUES(NULL, :image_path, :thumbnail_path, :description, :author_name, CURRENT_TIMESTAMP(), :user_id)';
        if ($filename = $this->upload($_FILES['image'])) {
            $width = 348;
            $height = 0;
            $params = [
                ':image_path' => $filename,
                ':thumbnail_path' => $this->thumbnail($filename, $width, $height),
                ':description' => $_REQUEST['description'],
                ':author_name' => $_REQUEST['authorname'],
                ':user_id' => $_SESSION['auth'],
            ];
            $this->request($sql, $params);

            App::get('session')->setMessage('You have uploaded new image');
            unset($_SESSION['fields']);

            return true;
        }
        App::get('session')->setError('Unable to upload image');

        return false;
    }

    public function upload($file)
    {
        if (!$this->createDir(IMAGE_RESOURCE_URL)) {
            return false;
        }

        $filename = IMAGE_RESOURCE_URL . time() . $file['name'];
        if (move_uploaded_file($file['tmp_name'], $filename)) {
            return $filename;
        }

        return false;
    }

    public function createDir($path)
    {
        if (!file_exists($path)) {
            return mkdir($path, 0777);
        }
        return true;
    }

}