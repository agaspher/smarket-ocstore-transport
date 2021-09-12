<?php

# Кастомный контроллер ocstore для получения списка хэшей
# изображений, которые уже загружены на хостинге и в базу
# чтобы не слать их лишний раз впустую
# должен лежать в папке www/catalog/controller/hash
# тогда урл будет https://site.com/index.php?route=hash/entry (названия папки и адрес связаны)

class ControllerHashEntry extends Controller
{
	public function index(){
		$query = $this->db->query("select image from ВЕРНОЕ_ИМЯ_БАЗЫ.oc_product;");


		$result = [];
		foreach($query->rows as $row) {
			$result[] = substr($row['image'], 8, -4);
		}

		return $this->response->setOutput(json_encode($result));
	}
}

