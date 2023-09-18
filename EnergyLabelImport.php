<?php

use Magento\Catalog\Model\ProductRepository;
use Magento\Framework\App\State;

/** @var \Magento\Framework\ObjectManagerInterface $objectManager */
$objectManager = require __DIR__ . DIRECTORY_SEPARATOR . 'BaseMethodCalling.php';

$header = 0;
const
ENTITY_ID = 'entity_id',
DATA_SHEET_URL = 'dt_url',
UPLOAD_IMAGE_URL = 'pic_url',
LABEL_COLOR = 'label_color',
LABEL_INDEX = 'label_index',
SKU = 'sku',
VIS_DATABLAD = 'Vis datablad',
READ_CSV_FILE_PATH = __DIR__ . DIRECTORY_SEPARATOR . "CSVs/Import/EnergyLabels.csv",
DATA_SHEET_FILE_PATH = "pub/media/energylabel/files/attachment/",
ENERGY_IMAGE_FILE_PATH = "pub/media/energylabel/files/attachment/",
HEADER = [
    'entity_id',
    'dt_url',
    'pic_url',
    'label_color',
    'label_index',
    'sku'
];
if (!file_exists(DATA_SHEET_FILE_PATH)) {
    mkdir(DATA_SHEET_FILE_PATH, 0777, true);
}
if (!file_exists(ENERGY_IMAGE_FILE_PATH)) {
    mkdir(ENERGY_IMAGE_FILE_PATH, 0777, true);
}
$state = $objectManager->get(State::class);
$state->setAreaCode('adminhtml');
$csvHeaders = array_flip(HEADER);
echo "********** Start Importing the Energy Labels **********" . PHP_EOL;
if (($open = fopen(READ_CSV_FILE_PATH, "r")) !== false) {
    while (($row = fgetcsv($open, 1000, ",")) !== false) {
        if ($header == 0) {
            $header++;
            continue;
        }
        // Initialize a file URL to the link
        $dataSheetUrl = $row[$csvHeaders[DATA_SHEET_URL]];
        $energyLabelImageUrl = $row[$csvHeaders[UPLOAD_IMAGE_URL]];
        $productSku = $row[$csvHeaders[SKU]];
        $labelColor = $row[$csvHeaders[LABEL_COLOR]];
        $LabelIndex = $row[$csvHeaders[LABEL_INDEX]];
        $dataSheetFilePath = DATA_SHEET_FILE_PATH . basename($dataSheetUrl);
        $energyLabelImagePath = BP . '/' . ENERGY_IMAGE_FILE_PATH . basename($energyLabelImageUrl);
        $trimEnergyLabelImagePath = ENERGY_IMAGE_FILE_PATH . basename($energyLabelImageUrl);
        $imagePath = "/" . substr($energyLabelImagePath, strpos($energyLabelImagePath, 'migration'));
        $product = $objectManager->create(ProductRepository::class);
        $initProduct = $product->get($productSku);
        if ($dataSheetUrl != '') {
            if (file_put_contents($dataSheetFilePath, file_get_contents($dataSheetUrl))) {
                echo basename($dataSheetUrl) . " Data sheet downloaded successfully" . PHP_EOL;
                $initProduct->setDataSheet('/' . basename($dataSheetUrl));
            } else {
                echo basename($dataSheetUrl) . " Data sheet downloading failed." . PHP_EOL;
            }
        }
        if ($energyLabelImageUrl != '') {
            if (file_put_contents($trimEnergyLabelImagePath, file_get_contents($energyLabelImageUrl))) {
                echo basename($trimEnergyLabelImagePath) . " Energy Label Image downloaded successfully" . PHP_EOL;
                $initProduct->setEnergyLabelImage('/' . basename($trimEnergyLabelImagePath));
            } else {
                echo basename($energyLabelImageUrl) . " Energy Label Image downloading failed." . PHP_EOL;
            }
        }
        $initProduct->setEnergyEfficiencyIndex($LabelIndex);
        $initProduct->setColorGrade($labelColor);
        $initProduct->save();
        $initProduct = null;
    }
}
echo "********** File Downloading is Ended **********" . PHP_EOL;
fclose($open);
