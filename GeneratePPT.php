<?php


//Importing library elements
use PhpOffice\PhpPresentation\Shape\Drawing\Base64;
use PhpOffice\PhpPresentation\IOFactory;
use PhpOffice\PhpPresentation\PhpPresentation;
use PhpOffice\PhpPresentation\Style\Alignment;
use PhpOffice\PhpPresentation\Style\Color;
use PhpOffice\PhpPresentation\Style\Border;
use PhpOffice\PhpPresentation\Style\Fill;


require_once 'libs/PhpPresentation/src/PhpPresentation/Autoloader.php';
\PhpOffice\PhpPresentation\Autoloader::register();
require_once 'libs/Common/src/Common/Autoloader.php';
\PhpOffice\Common\Autoloader::register();
//



$json = file_get_contents('php://input');
$skeletons = json_decode($json);

// $data = json_decode($json);

// $skeletons = $data->skeletons;
// $solutions = $data->solutions;

$presentation = new PhpPresentation();


foreach ($skeletons as $i => $skeleton) {
    // Create slide
    if ($i == 0) {
        $currentSlide = $presentation->getActiveSlide();
    } else {
        $currentSlide = $presentation->createSlide();
    }

    //Add Title and style it
    $textShape = $currentSlide->createRichTextShape();
    $textShape
        ->setHeight(200)
        ->setWidth(600)
        ->setOffsetX(10)
        ->setOffsetY(10);
    $textShape->getActiveParagraph()->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);
    //if inline to check if we have more than one skeleton, if so, get the index of the loop and add 1 
    //(since loops start with index 0), else the variable number will hold the '' empty value
    $number = count($skeletons) > 1 ? $i + 1 : '';

    $textRun = $textShape->createTextRun('Skeleton ' . $number);
    $textRun->getFont()
        ->setBold(true)
        ->setSize(28)
        ->setColor(new Color('FF0D6EFD'));

    //Add Skeleton Image
    $rows = $skeleton->puzzle;
    $rows_count = count($rows);
    $columns_count = count(array_keys((array)$rows[0]));

    // Create a shape (table)
    $shape = $currentSlide->createTableShape($columns_count); //number of columns
    $shape->setHeight(50 * $rows_count);
    $shape->setOffsetX((960 - $columns_count * 50) / 2);
    $shape->setOffsetY(200);


    foreach ($rows as $i => $row) {
        // Add row
        $tableRow = $shape->createRow();
        $tableRow->setHeight(50);

        foreach ($row as $k => $col) {
            $oCell = $tableRow->nextCell();
            $oCell->setWidth(50);
            if ($col === 0) {

                $oCell->getFill()->setFillType(Fill::FILL_SOLID)
                    ->setStartColor(new Color('FF000000'))
                    ->setEndColor(new Color('FF000000'));
            }
            $textRun = $oCell->createTextRun('');
            $textRun->getFont()
                ->setBold(true)
                ->setSize(20);

            $oCell->getActiveParagraph()
                ->getAlignment()
                ->setHorizontal(Alignment::HORIZONTAL_CENTER)
                ->setVertical(Alignment::VERTICAL_CENTER);
        }
    }
}
foreach ($skeletons as $i => $skeleton) {
    // Create slide
    $currentSlide = $presentation->createSlide();

    //Add Title and style it
    $textShape = $currentSlide->createRichTextShape();
    $textShape
        ->setHeight(200)
        ->setWidth(600)
        ->setOffsetX(10)
        ->setOffsetY(10);
    $textShape->getActiveParagraph()->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);
    //if inline to check if we have more than one skeleton, if so, get the index of the loop and add 1 
    //(since loops start with index 0), else the variable number will hold the '' empty value
    $number = count($skeletons) > 1 ? $i + 1 : '';
    $textRun = $textShape->createTextRun('Solution ' . $number);
    $textRun->getFont()
        ->setBold(true)
        ->setSize(28)
        ->setColor(new Color('FF0D6EFD'));

        $rows = $skeleton->solution;
        $rows_count = count($rows);
        $columns_count = count(array_keys((array)$rows[0]));
    
        // Create a shape (table)
        $shape = $currentSlide->createTableShape($columns_count); //number of columns
        $shape->setHeight(50 * $rows_count);
        $shape->setOffsetX((960 - $columns_count * 50) / 2);
        $shape->setOffsetY(200);
    
    
        foreach ($rows as $i => $row) {
            // Add row
            $tableRow = $shape->createRow();
            $tableRow->setHeight(50);
    
            foreach ($row as $k => $col) {
                $oCell = $tableRow->nextCell();
                $oCell->setWidth(50);
                if ($col === 0) {
                    $oCell->getBorders()->getLeft()->setLineStyle(Border::LINE_NONE);
                    $oCell->getBorders()->getTop()->setLineStyle(Border::LINE_NONE);
                    $oCell->getBorders()->getRight()->setLineStyle(Border::LINE_NONE);
                    $oCell->getBorders()->getBottom()->setLineStyle(Border::LINE_NONE);
                    $oCell->getFill()->setFillType(Fill::FILL_SOLID)
                        ->setStartColor(new Color('FF000000'))
                        ->setEndColor(new Color('FF000000'));
                } else {
                    $oCell->getBorders()->getLeft()->setLineStyle(Border::LINE_SINGLE);
                    $oCell->getBorders()->getTop()->setLineStyle(Border::LINE_SINGLE);
                    $oCell->getBorders()->getRight()->setLineStyle(Border::LINE_SINGLE);
                    $oCell->getBorders()->getBottom()->setLineStyle(Border::LINE_SINGLE);
                    $oCell->getFill()->setFillType(Fill::FILL_SOLID)
                        ->setStartColor(new Color('FFF5F5F5'))
                        ->setEndColor(new Color('FFF5F5F5'));
                    $textRun = $oCell->createTextRun($col);
                    $textRun->getFont()
                        ->setSize(20);
    
                    $oCell->getActiveParagraph()
                        ->getAlignment()
                        ->setHorizontal(Alignment::HORIZONTAL_CENTER)
                        ->setVertical(Alignment::VERTICAL_CENTER);
                }
            }
        }
    }






$writerPPTX = IOFactory::createWriter($presentation, 'PowerPoint2007');

$postfix = count($skeletons) > 1 ? 'N' : '1';
$filename = 'skeleton_' . $postfix . '_' . time() . '.pptx';
$writerPPTX->save('ppt/' . $filename);

echo $filename;
