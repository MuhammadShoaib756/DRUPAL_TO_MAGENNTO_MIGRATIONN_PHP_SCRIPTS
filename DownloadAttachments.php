<?php

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
ini_set('memory_limit', '2G');
error_reporting(E_ALL);

$header = 0;
const
IMPORT_FILE_ID = 'import_file_id',
IMPORT_ID = 'import_id',
STORE_ID = 'store_id',
FILENAME = 'filename',
LABEL = 'label',
CUSTOMER_GROUPS = 'customer_groups',
IS_VISIBLE = 'is_visible',
INCLUDE_IN_ORDER = 'include_in_order',
PRODUCT_IDS = 'product_ids',
CATEGORY_IDS = 'category_ids',
PRODUCT_SKUS = 'product_skus',
LINK = 'link',
READ_CSV_FILE_PATH = __DIR__ . DIRECTORY_SEPARATOR . "CSVs/Import/CategoryAndProductAttachments.csv",
PDF_STORE_FILE_PATH = "pub/media/amasty/amfile/import/ftp/",
HEADER = [
    'import_file_id',
    'import_id',
    'store_id',
    'filename',
    'label',
    'customer_groups',
    'is_visible',
    'include_in_order',
    'product_ids',
    'category_ids',
    'product_skus',
    'link'
];

$csvHeaders = array_flip(HEADER);
echo "********** File Downloading is started **********" . PHP_EOL;
if (($open = fopen(READ_CSV_FILE_PATH, "r")) !== false) {
    while (($row = fgetcsv($open, 1000, ",")) !== false) {
        if ($header == 0) {
            $header++;
            continue;
        }
        // Initialize a file URL to the link
        $url = $row[$csvHeaders[LINK]];
        $file_name = PDF_STORE_FILE_PATH . $row[$csvHeaders[FILENAME]] . '.pdf';
        if (file_put_contents($file_name, file_get_contents($url))) {
            echo "{$file_name} downloaded successfully" . PHP_EOL;
        } else {
            echo "{$file_name} downloading failed." . PHP_EOL;
        }
    }
}
echo "********** File Downloading is Ended **********" . PHP_EOL;
fclose($open);
