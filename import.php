<?php

namespace Magazinera;

include 'config.php';

use Magazinera\MagazineraConfig as Config;
use PDO;


$db = get_connection(Config::OCS_DB, Config::OCS_DB_USER, Config::OCS_DB_PASS);

loadDataFromJsonToOcStore($db);


function getArrayIdFromProducts($ocstore_db)
{
    $sql = "select product_id, model, sku from oc_product;";
    $aProducts = [];

    foreach ($ocstore_db->query($sql) as $product) {
        $aProducts[$product['sku']] = [
            'id' => $product['product_id'],
            'articul' => $product['sku'],
            'name' => $product['model']
        ];
    }

    return $aProducts;
}

function loadDataFromJsonToOcStore($ocstore_db, $clear = False)
{
    if ($clear) {
        deleteAllProducts($ocstore_db);
    }

    $f_raw = file_get_contents(Config::IMP_JSON_DIR . '/cards.json');
    $aProducts = json_decode($f_raw, true);

    loadOcProducts($ocstore_db, $aProducts);
    loadOcProductsDescription($ocstore_db, $aProducts);
    loadOcCategories($ocstore_db);
}


function loadOcProducts($ocstore_db, $products)
{
    $dbProducts = [];
    $qr = "select sku from oc_product;";
    foreach ($ocstore_db->query($qr) as $rec) {
        array_push($dbProducts, $rec['sku']);
    };

    foreach ($products as $product) {
        if (sizeof($product['fotos']) == 1) {
            $image = Config::OCS_IMG_PATH . $product['fotos'][0];
        } else {
            $image = '';
        }

        if (in_array($product['articul'], $dbProducts)) {
            $qr = "
            update oc_product set model='{$product['articul']}', image='{$image}', 
            price={$product['price']}, quantity={$product['quantity']}
            where sku='{$product['articul']}';";
        } else {
            $qr = "insert into oc_product (
                model, sku, upc, ean, jan, isbn, mpn, location, quantity, 
                stock_status_id, image, manufacturer_id, shipping, price, 
                points, tax_class_id, date_available, weight, weight_class_id, 
                length, width, height, length_class_id, subtract, minimum, 
                sort_order, status, viewed, date_added, date_modified)
                values ('{$product['articul']}', '{$product['articul']}', '', '', '', '', '', '', {$product['quantity']}, 
                5, '{$image}', 0, 1, {$product['price']}, 
                0, 0, '2020-08-10', 0, 1, 
                0, 0, 0, 1, 1, 1, 
                0, 1, 0, '2020-08-10', '2020-08-10');";
        }

        $state = $ocstore_db->prepare($qr);
        $status = $state->execute();
    }
}


function loadOcProductsDescription($ocstore_db, $aProducts)
{
    $dbProducts = getArrayIdFromProducts($ocstore_db);
    $prodIdsFromDesc = [];
    $qr = "select product_id from oc_product_description;";
    foreach ($ocstore_db->query($qr) as $rec) {
        array_push($prodIdsFromDesc, $rec['product_id']);
    };

    foreach ($dbProducts as $product) {
        $productInfo = array_filter($aProducts, function ($innerArray) use ($product) {
            return ($innerArray['articul'] == $product['articul']);
        });

        if ($productInfo) {
            $info = array_values($productInfo)[0]['info'];
            $productName = array_values($productInfo)[0]['name'];
        } else {
            $info = '';
            $productName = $product['name'];
        };

        $productId = $product['id'];

        if (in_array($productId, $prodIdsFromDesc)) {
            $qr = "update oc_product_description set name='{$productName}', description='{$info}', meta_title='{$productName}' where product_id={$productId};";
        } else {
            $qr = "insert into oc_product_description (product_id, language_id, name, description, tag, meta_title, meta_description, meta_keyword) 
                values ('{$productId}', 1, '{$productName}', '{$info}', '', '{$productName}', '', '');";
        }

        $state = $ocstore_db->prepare($qr);
        $state->execute();

        if (in_array($productId, $prodIdsFromDesc)) {
            continue;
        }

        $sql = "insert into oc_product_to_store (product_id, store_id) values ('{$productId}', 0);";

        $state = $ocstore_db->prepare($sql);
        $status = $state->execute();

        $classif = '';
        foreach ($aProducts as $p) {
            if ($p['articul'] == $product['articul']) {
                $classif = $p['classif'];
            };
        };

        $sql = "insert into oc_product_to_category (product_id, category_id, main_category) 
                                            values ('{$productId}', '{$classif}', 1);";

        $state = $ocstore_db->prepare($sql);
        $status = $state->execute();
    }
}


function deleteEmptyCategories($ocstore_db)
{
    $category_id_with_products = [];
    $sql = "select distinct category_id from oc_product_to_category";
    foreach ($ocstore_db->query($sql) as $id) {
        array_push($category_id_with_products, $id['category_id']);
    };

    $category_id_with_subcategories = [];
    $sql = "select distinct parent_id from oc_category";
    foreach ($ocstore_db->query($sql) as $id) {
        array_push($category_id_with_subcategories, $id['parent_id']);
    };

    $categories = [];
    $sql = "select distinct category_id from oc_category";
    foreach ($ocstore_db->query($sql) as $id) {
        array_push($categories, $id['category_id']);
    };

    $empty_categories = [];
    foreach ($categories as $key => $value) {
        if (!(in_array($value, $category_id_with_products) || in_array($value, $category_id_with_subcategories))) {
            array_push($empty_categories, $value);
        };
    };

    foreach ($empty_categories as $key => $value) {
        $sql = "delete from oc_category where category_id = '{$value}';
                delete from oc_category_description where category_id = '{$value}';
                delete from oc_category_to_store where category_id = '{$value}';";

        $state = $ocstore_db->prepare($sql);
        $state->execute();
    };

    return $empty_categories;
}


function deleteLoop($ocstore_db)
{
    $result = deleteEmptyCategories($ocstore_db);

    if ($result) {
        deleteLoop($ocstore_db);
    }
}


function loadOcCategories($ocstore_db)
{
    $f_raw = file_get_contents(Config::IMP_JSON_DIR . '/classif.json');
    $classif = json_decode($f_raw, true);

    $sql = "delete from oc_category; delete from oc_category_description; delete from oc_category_to_store";

    $state = $ocstore_db->prepare($sql);
    $state->execute();

    foreach ($classif as $category) {
        $sql = "insert into oc_category (category_id, image, parent_id, top, `column`, sort_order, status, date_added, date_modified) 
                values ('{$category['category_id']}', '', '{$category['parent_id']}', 1, 1, 0, 1, '2017-02-01', '2017-02-01')";


        $state = $ocstore_db->prepare($sql);
        $state->execute();

        $sql = "insert into oc_category_description (category_id, language_id, name, description, meta_title, meta_description, meta_keyword) 
                values ('{$category['category_id']}', 1, '{$category['name']}', '', '', '', '')";


        $state = $ocstore_db->prepare($sql);
        $state->execute();

        $sql = "insert into oc_category_to_store (category_id, store_id)
                values ('{$category['category_id']}', 0)";

        $state = $ocstore_db->prepare($sql);
        $state->execute();

        $sql = "insert into oc_category_path (category_id, path_id, level)
                values ('{$category['category_id']}', '{$category['parent_id']}', 0)";

        $state = $ocstore_db->prepare($sql);
        $state->execute();

        $sql = "insert into oc_category_path (category_id, path_id, level)
                values ('{$category['category_id']}', '{$category['category_id']}', 1)";

        $state = $ocstore_db->prepare($sql);
        $state->execute();
    };

    deleteEmptyCategories($ocstore_db);
}

function deleteAllProducts($ocstore_db)
{
    $sql = [
        "delete from oc_product_to_store;",
        "delete from oc_product_to_category;",
        "delete from oc_product_description;",
        "delete from oc_product;",
    ];

    $state = $ocstore_db->prepare(join($sql, ""));
    $state->execute();
}

function get_connection($uri, $db_user, $db_pass)
{
    return new PDO($uri, $db_user, $db_pass);
}
