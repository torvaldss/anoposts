<?php
// тут вывод ошибок, в продакшене лучше убрать
error_reporting(E_ALL);
ini_set('display_errors', 1);

try {
    $postsFile = 'data/posts.json';
    if (!file_exists($postsFile)) {
        file_put_contents($postsFile, '[]');
    }

    $postsData = file_get_contents($postsFile);
    if ($postsData === false) {
        throw new Exception("Не удалось прочитать файл с постами");
    }

    $posts = json_decode($postsData, true);
    if (json_last_error() !== JSON_ERROR_NONE) {
        throw new Exception("Ошибка формата JSON: " . json_last_error_msg());
    }

    $posts = is_array($posts) ? array_reverse($posts) : [];

} catch (Exception $e) {
    error_log("Ошибка в posts.php: " . $e->getMessage());
    $error = "Произошла ошибка при загрузке постов. Пожалуйста, попробуйте позже.";
    $posts = [];
}
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Все посты | Анонимная доска</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="container">
        <h1>Все посты</h1>
        
        <div class="buttons">
            <a href="index.php" class="btn">На главную</a>
            <a href="index.php#create-post" class="btn">Создать пост</a>
        </div>

        <?php if (isset($error)): ?>
            <div class="alert error"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>
        
        <?php if (empty($posts)): ?>
            <div class="alert info">Пока нет ни одного поста. Будьте первым!</div>
        <?php else: ?>
            <div class="posts">
                <?php foreach ($posts as $post): ?>
                    <?php 
                    $postId = $post['id'] ?? uniqid();
                    $title = $post['title'] ?? 'Без названия';
                    $content = $post['content'] ?? '';
                    $date = $post['date'] ?? date('Y-m-d H:i:s');
                    
                    $commentCount = 0;
                    $commentFile = "data/comments/{$postId}.json";
                    if (file_exists($commentFile)) {
                        $comments = json_decode(file_get_contents($commentFile), true) ?: [];
                        $commentCount = count($comments);
                    }
                    ?>
                    
                    <div class="post">
                        <h3><a href="post.php?id=<?= htmlspecialchars($postId) ?>">
                            <?= htmlspecialchars($title) ?>
                        </a></h3>
                        
                        <div class="meta">
                            <span class="date"><?= date('d.m.Y H:i', strtotime($date)) ?></span>
                            <span class="comments-count"><?= $commentCount ?> комментариев</span>
                        </div>
                        
                        <p><?= nl2br(htmlspecialchars(substr($content, 0, 200))) ?>
                            <?= strlen($content) > 200 ? '...' : '' ?>
                        </p>
                        
                        <a href="post.php?id=<?= htmlspecialchars($postId) ?>" class="btn small">
                            Читать далее
                        </a>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</body>

</html>
