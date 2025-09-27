<?php

use Ralbum\Search;
use Ralbum\Setting;
use Ralbum\Model\Image;
use Ralbum\Model\File;

define('BASE_DIR', dirname(__FILE__));

require 'app/vendor/autoload.php';

if (php_sapi_name() != 'cli') {
    die('此文件应在命令行模式下运行');
}

echo "\n\n开始处理\n";
echo "--------------------------\n";
echo date('Y-m-d H:i:s') . "\n";

if (Search::isSupported()) {
    // reset search index
    $search = new Search();
    $search->resetIndex();
} else {
    $search = [];
}


function updateRecursively($baseDir, $search)
{
    foreach (scandir($baseDir) as $file) {
        try {
            if (substr($file, 0, 1) == '.') {
                continue;
            }

            $fullPath = $baseDir . '/' . $file;

            if (is_dir($fullPath)) {
                updateRecursively($fullPath, $search);
                continue;
            }

            $file = new File($fullPath);

            if (in_array($file->getExtension(), Setting::get('supported_extensions'))) {
                $file = new Image($fullPath);

                echo "正在处理图片 -> " . $fullPath . "\n";

                // to speed up the cron process the detail images are not generated if the default option is full size
                if (!Setting::get('full_size_by_default')) {
                    $file->updateDetail();
                }
                $file->updateThumbnail();

            }

            if (Search::isSupported()) {
                $file->updateIndex($search);
            }
        } catch (Exception $e) {
            echo "处理文件时出错: " . $fullPath . "\n";
            echo $e->getMessage() . "\n";
        }
    }
}

updateRecursively(Setting::get('image_base_dir'), $search);

echo "\n\n处理完成\n";
echo "--------------------------\n";
echo "请确保您对缓存文件夹及其所有子文件夹和文件设置了正确的权限\n";
echo "以便用户使用apache进程运行时能够写入文件夹\n";
