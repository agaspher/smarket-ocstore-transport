<?php

declare(strict_types=1);

namespace Globus\LoadManager;

include(ABS_PATH . '/LoadManager/Loader/JsonLoader.php');

use Globus\GlobusConfig as Config;
use Globus\LoadManager\Loader\JsonLoader;
use PDO;

class LoadManager
{
    private $db;

    /**
     * @var JsonLoader
     */
    public $loader;

    /**
     * @var bool
     */
    private $debug = false;

    /**
     * @var array
     */
    private $products = [];

    /**
     * @var array
     */
    private $categories = [];

    /**
     * @throws \Exception
     */
    public function __construct(bool $debug = false)
    {
        $this->loader = new JsonLoader();
        $this->db = $this->get_connection(Config::OCS_DB, Config::OCS_DB_USER, Config::OCS_DB_PASS);

        $this->debug = $debug;
        $this->products = $this->loader->getProducts();
        $this->categories = $this->loader->getCategories();

        $this->logFile = fopen('globus.log', 'w');

        $this->log("================start process " . date("Y-m-d H:i:s") . "====================");
        $this->log(sprintf('Found %s products.', count($this->products)));
        $this->log(sprintf('Found %s categories.', count($this->categories)));
    }

    public function __destruct()
    {
        fwrite($this->logFile, "================end process " . date("Y-m-d H:i:s") . "======================");
        fclose($this->logFile);
    }

    public function load()
    {
        $this->loadProductsToOcStoreDb();
        //var_dump($this->categories);
    }

    function loadProductsToOcStoreDb()
    {
        $dbProducts = [];
        $qr = "select sku from oc_product;";
        foreach ($this->db->query($qr) as $rec) {
            array_push($dbProducts, $rec['sku']);
        };

        foreach ($this->products as $product) {
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

            $this->runQuery($qr);
        }

        $this->loadProductsDescToOcStoreDb();
    }

    function loadProductsDescToOcStoreDb()
    {
        $dbProducts = $this->getDbProducts();
        $prodIdsFromDesc = [];
        $productIdsCategory = [];

        $qr = "select product_id from oc_product_description;";
        foreach ($this->db->query($qr) as $rec) {
            array_push($prodIdsFromDesc, $rec['product_id']);
        };

        $qr = "select distinct product_id from oc_product_to_category;";
        foreach ($this->db->query($qr) as $rec) {
            array_push($productIdsCategory, $rec['product_id']);
        };

        foreach ($dbProducts as $product) {
            $productInfo = array_filter($this->products, function ($innerArray) use ($product) {
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

            $this->runQuery($qr);

            // привязка к категории
            $classif = '';
            foreach ($this->products as $p) {
                if ($p['articul'] == $product['articul']) {
                    $classif = $p['classif'];
                };
            };

            if (in_array($productId, $productIdsCategory)) {
                $sql = "update oc_product_to_category set category_id = '{$classif}' where product_id = '{$productId}';";
            } else {
                $sql = "insert into oc_product_to_category (product_id, category_id) values ('{$productId}', '{$classif}');";
            }

            $this->runQuery($sql);

            if (in_array($productId, $prodIdsFromDesc)) {
                continue;
            }

            $sql = "insert into oc_product_to_store (product_id, store_id) values ('{$productId}', 0);";

            $this->runQuery($sql);
        }
    }

    /**
     * @return array
     *
     * [
     *      'sku' => [
     *          'id' => 123,
     *          'articul' => '45678',
     *          'name' => 'name'
     *      ]
     * ]
     *
     */
    public function getDbProducts(): array
    {
        $sql = "select product_id, model, sku from oc_product;";
        $result = [];

        foreach ($this->db->query($sql) as $product) {
            $result[$product['sku']] = [
                'id' => $product['product_id'],
                'articul' => $product['sku'],
                'name' => $product['model']
            ];
        }

        return $result;
    }

    public function log($message)
    {
        if ($this->debug) {
            fwrite($this->logFile, $message . PHP_EOL);
        }
    }

    public function get_connection($uri, $db_user, $db_pass): PDO
    {
        return new PDO($uri, $db_user, $db_pass);
    }

    public function runQuery($query)
    {
        $this->log($query);
        ($this->db->prepare($query))->execute();
    }
}