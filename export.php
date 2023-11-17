<?php

namespace Globus;

include 'import.config.php';

use CURLFile;
use Globus\GlobusConfig as Config;
use PDO;


function get_connection($uri, $db_user, $db_pass)
{
    return new PDO($uri, $db_user, $db_pass);
}

$sm_db = get_connection(Config::SM_DB, Config::SM_DB_USER, Config::SM_DB_PASS);
$sm_db_image = get_connection(Config::SM_IMG_DB, Config::SM_DB_USER, Config::SM_DB_PASS);

function exportCardsFromSmToJson($sm_db, $sm_db_image)
{
    $cards = [];

    $query = "select distinct cs.articul, csi.info, ccf.classif, cs.mesuriment, cs.shortname, dc.price_rub, ct.name_country 
            from cardscla cs
       left join country ct on cs.country=ct.id_country
            left join disccard dc on cs.articul=dc.articul
			left join cardclassif ccf on ccf.articul = cs.articul
			left join cardscla_info csi on cs.articul=csi.articul
			left join ostatok_short os on cs.articul=os.articul
           where cs.is_accept like 'T' and dc.price_kind=" . Config::PRICE_KIND . " 
            and ccf.classif_type=" . Config::CLASSIF_TYPE . "and dc.price_rub <> 0 
            and os.place_index=" . Config::PLACE_INDEX . " and cs.articul in (
           select distinct articul from cardfoto);";

    $count = 0;
    foreach ($sm_db->query($query) as $card) {
        $query = "select cf.id from cardfoto cf where articul like '" . $card['ARTICUL'] . "'";
        $fotos_id = [];
        $fotos_path = [];

        foreach ($sm_db->query($query) as $id) {
            array_push($fotos_id, $id[0]);
        }

        $query_image = "select data_foto from cardfoto where id in (" . implode(', ', $fotos_id) . ")";

        if ($fotos_id) {
            foreach ($sm_db_image->query($query_image) as $image) {
                $count++;
                file_put_contents(Config::SAVE_IMG_DIR . '/tmp.tmp', $image);

                $tfile = fopen(Config::SAVE_IMG_DIR . '/tmp.tmp', 'r');
                $l100 = fread($tfile, filesize(Config::SAVE_IMG_DIR . '/tmp.tmp'));
                fclose($tfile);
                unlink(Config::SAVE_IMG_DIR . '/tmp.tmp');


                print_r(hash('md5', $l100), true);
                $hash_file_for_name = print_r(hash('md5', $l100), true);
                $file_name = $hash_file_for_name . ".jpg";
                file_put_contents(Config::SAVE_IMG_DIR . '/' . $file_name, $image);

                array_push($fotos_path, $file_name);
            }
        }

        $cards[] = [
            'articul' => $card['ARTICUL'],
            'classif' => $card['CLASSIF'],
            'mesuriment' => $card['MESURIMENT'],
            'name' => mb_convert_encoding($card['SHORTNAME'], 'utf-8', 'windows-1251'),
            'info' => mb_convert_encoding($card['INFO'], 'utf-8', 'windows-1251'),
            'price' => $card['PRICE_RUB'],
            'country' => mb_convert_encoding($card['NAME_COUNTRY'], 'utf-8', 'windows-1251'),
            'fotos' => $fotos_path,
            'remains' => 5,
        ];
    }

    $fp = fopen(Config::SAVE_JSON_DIR . '/cards.json', 'w');
    fwrite($fp, json_encode($cards, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));
    fclose($fp);
}

function exportClassifFromSmToJson($sm_db) {
    $query = "select cf.id_classif, cf.parent_classif, cf.name_classif from classif cf where cf.type_classif = ".Config::TYPE_CLASSIF.";";

    $classif = [];
    foreach ($sm_db->query($query) as $classif_record) {
        $classif[] = [
            'category_id' => $classif_record['ID_CLASSIF'],
            'parent_id' => $classif_record['PARENT_CLASSIF'],
            'name' => mb_convert_encoding($classif_record['NAME_CLASSIF'], 'utf-8', 'windows-1251')
        ];
    }

    $fp = fopen(Config::SAVE_JSON_DIR . '/classif.json', 'w');
    fwrite($fp, json_encode($classif, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));
    fclose($fp);
}

function exportSizes($conn)
{
    $query = <<<SQL
        select distinct orr.articul as ARTICUL,  r.rus as RUS
        from ostatok_rz orr
        left join cardasrtattribsp crp on crp.articul=orr.articul and crp.asrt=orr.rz and crp.attrib=0
        left join rz r on r.id=crp.attribsp
        left join disccard dc on dc.articul=orr.articul and dc.quantity=0 and dc.price_kind=0
        left join cardscla cll on orr.articul=cll.articul
        left join clients cl on cl.id_clients = orr.place_index
        left join CARDCLASSIF ccf on ccf.articul=orr.articul
        where orr.quantity>0 and orr.rz<>0 and r.name is not null and
        ccf.classif_type=1001
        order by orr.articul, r.rus
SQL;

    $sizes = [];
    foreach ($conn->query($query) as $row) {
        $sizes[] = [
            'articul' => $row['ARTICUL'],
            'rus' => $row['RUS']
        ];
    }

    $fp = fopen(Config::SAVE_JSON_DIR . '/sizes.json', 'w');
    fwrite($fp, json_encode($sizes, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));
    fclose($fp);
}

exportCardsFromSmToJson($sm_db, $sm_db_image);
print("cards exports is done...");
exportClassifFromSmToJson($sm_db);
print("classif exports is done...");
exportSizes($sm_db);
print("size export is done...");

// close connection
$sm_db = null;

$output = shell_exec("7z a -tTAR " . Config::ARCHIEVE . " data");
print("archieved...");

$curl_file = new CURLFile(Config::ARCHIEVE);
$data = [
    "verify" => Config::VERIFY,
    "file" => $curl_file,
];

$ch = curl_init();

curl_setopt($ch, CURLOPT_URL, Config::UPLOAD_URL);
curl_setopt($ch, CURLOPT_POST, 1);
curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

$result = curl_exec($ch);

curl_close($ch);

print_r("sended...");
