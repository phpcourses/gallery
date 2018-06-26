<!DOCTYPE html>
<html>
<head>
    <title><?php echo $title ?></title>
    <link rel="stylesheet" href="pub/css/bootstrap.css">
    <link rel="stylesheet" href="pub/css/main.css">
    <script src="//code.jquery.com/jquery-3.3.1.min.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/fancybox/3.3.5/jquery.fancybox.min.css"/>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/fancybox/3.3.5/jquery.fancybox.min.js"></script>
</head>
<body>
<div class="album py-5 bg-light">
    <div class="container">
        <ul class="nav justify-content-end">
            <?php if (App::get('session')->isLoggedIn()): ?>
                <li class="nav-item">
                    <a class="nav-link" href="/logout">Log out</a>
                </li>
            <?php else: ?>
                <li class="nav-item">
                    <a class="nav-link" href="/login">Login</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="/register">Registration</a>
                </li>
            <?php endif; ?>
        </ul>
        <h1 class="h1 text-center"><?php echo $title ?></h1>
        <?php if (App::get('session')->isLoggedIn()) : ?>
            <a class="btn btn-dark btn-lg active m-md-2" href="/form">Upload New Image</a>
        <?php endif; ?>
        <?php if ($messages = App::get('session')->messages()): ?>
            <div class="alert alert-success">
                <?php echo $messages ?>
            </div>
        <?php endif; ?>
        <div class="row">
            <?php if (!empty($images = $image->getCollection())): ?>
                <?php foreach ($images as $image): ?>
                    <div class="col-md-4">
                        <div class="card mb-4 box-shadow">
                            <a data-fancybox="gallery"
                               href="<?php echo $image['image_path'] ?>">
                                <img class="card-img-top" alt="Image" src="<?php echo $image['thumbnail_path'] ?>">
                            </a>
                            <div class="card-body">
                                <p class="card-text">Author: <?php echo $image['author_name'] ?>,
                                    Description: <?php echo $image['description'] ?>,
                                    Owner: <?php echo $image['login'] ?>
                                    Created at: <?php echo $image['created_at'] ?>
                                </p>
                            </div>
                            <?php if (App::get('session')->isLoggedIn()): ?>
                                <button type="button" class="btn btn-danger btn-xs"
                                        onclick="if (confirm('Are you sure?')) {location.href = '/removeImage?id=<?php echo $image['id'] ?>';}">
                                    Delete
                                </button>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="alert alert-danger col-12">
                    There are no images yet :P
                </div>
            <?php endif; ?>
        </div>
        <div class="d-flex p-2">
            <nav>
                <ul class="pagination justify-content-center">
                    <?php echo $pagination->render() ?>
                </ul>
            </nav>
        </div>
    </div>
</div>
</body>
</html>