<?php

# Кастомный контроллер ocstore для загрузки архива с картинками и json на хостинг
# если появляются проблемы с размером файла, то проверить настройки php
# max_file_upload, post_max_size  (называния не точные)
# должен лежать в папке www/catalog/controller/sm_update
# тогда урл будет https://site.com/index.php?route=sm_upload/entry (названия папки и адрес связаны)
 
class ControllerHashEntry extends Controller
{
    public function index()
    {
        if ($_POST['verify'] !== "Y2Y1tTfgqx4ArW4OXwMgp2t8BZR4HQd6") {
            return $this->response->setOutput('invalid verify');
        }

        $file = basename($_FILES['file']['name']);

        if (move_uploaded_file($_FILES['file']['tmp_name'], $file)) {
            echo "File is valid, and was successfully uploaded.\n";
        } else {
            echo "Possible file upload attack!\n";
        }

        return $this->response->setOutput('');
    }
}

