<?php

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
ini_set('memory_limit', '2G');
error_reporting(E_ALL);

use Magento\Catalog\Model\ResourceModel\Category\CollectionFactory;
use Magento\Framework\App\Bootstrap;

require 'app/bootstrap.php';

$bootstrap = Bootstrap::create(BP, $_SERVER);
$objectManager = $bootstrap->getObjectManager();
 /** @var CollectionFactory $categoryCollection */
$categoryCollection = $objectManager->create(CollectionFactory::class)->create();

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
WRITE_CSV_FILE_PATH = __DIR__ . DIRECTORY_SEPARATOR . "CSVs/Import/Attachment/FinalAttachments.csv",
READ_CSV_FILE_PATH = __DIR__ . DIRECTORY_SEPARATOR . "CSVs/Import/CategoryAndProductAttachments.csv",
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
    'product_skus'
];
$importFileId = 1;
$currentImportId = 1;
$header = 0;

$dir = dirname(WRITE_CSV_FILE_PATH);
echo "**** Current Directory: {$dir}" . PHP_EOL;
if (!is_dir($dir)) {
    echo "**** Creating Directory: {$dir}" . PHP_EOL;
    mkdir($dir, 0777, true);
}
echo "**** HEADER: {" . implode(', ', HEADER) . "}" . PHP_EOL;
$file = fopen(WRITE_CSV_FILE_PATH, 'w');
fputcsv($file, HEADER);

echo "************** Start Reading the File: " . READ_CSV_FILE_PATH . " *******" . PHP_EOL;

$import = [];

$arrayKeys = array_map(function () {
    return '';
}, array_flip(HEADER));

$csvHeaders = array_flip(HEADER);
if (($open = fopen(READ_CSV_FILE_PATH, "r")) !== false) {
    while (($data = fgetcsv($open, 1000, ",")) !== false) {
        if ($header == 0) {
            $header++;
            continue;
        }
        $import = array_map(function () {
            return '';
        }, array_flip($csvHeaders));
        $categoryIds = explode(',', $data[$csvHeaders[CATEGORY_IDS]]);
        foreach ($categoryIds as $categoryId) {
            $categoryId = trim($categoryId);
            $categoryCollection = $objectManager->create(CollectionFactory::class)->create();
            $import[$csvHeaders[CATEGORY_IDS]] = $import[$csvHeaders[CATEGORY_IDS]] . ',' . getCategoryId($categoryId, $categoryCollection);
            $import[$csvHeaders[CATEGORY_IDS]] = trim($import[$csvHeaders[CATEGORY_IDS]], ',');
        }
        $import[$csvHeaders[IMPORT_FILE_ID]] = $importFileId++;
        $import[$csvHeaders[IMPORT_ID]] = $currentImportId;
        $import[$csvHeaders[STORE_ID]] = $data[$csvHeaders[STORE_ID]];
        $import[$csvHeaders[FILENAME]] = $data[$csvHeaders[FILENAME]];
        $import[$csvHeaders[LABEL]] = $data[$csvHeaders[LABEL]];
        $import[$csvHeaders[IS_VISIBLE]] = $data[$csvHeaders[IS_VISIBLE]];
        $import[$csvHeaders[PRODUCT_SKUS]] = $data[$csvHeaders[PRODUCT_SKUS]];
        fputcsv($file, $import);
    }
}
echo "************** End Reading the File: " . READ_CSV_FILE_PATH . " *******" . PHP_EOL;
fclose($open);
fclose($file);

function getCategoryId($categoryId, $categoryCollection)
{
    $category = $categoryCollection->addAttributeToSelect('*')
        ->addAttributeToFilter('drupalentityid', $categoryId);
    return current($category->getItems()) ? current($category->getItems())->getId() : "";
}
