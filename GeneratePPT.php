<?php


//Importing library elements
use PhpOffice\PhpPresentation\Shape\Drawing\Base64;
use PhpOffice\PhpPresentation\IOFactory;
use PhpOffice\PhpPresentation\PhpPresentation;
use PhpOffice\PhpPresentation\Style\Alignment;
use PhpOffice\PhpPresentation\Style\Color;
use PhpOffice\PhpPresentation\Style\Border;
use PhpOffice\PhpPresentation\Style\Borders;
use PhpOffice\PhpPresentation\Style\Fill;


require_once 'libs/PhpPresentation/src/PhpPresentation/Autoloader.php';
\PhpOffice\PhpPresentation\Autoloader::register();
require_once 'libs/Common/src/Common/Autoloader.php';
\PhpOffice\Common\Autoloader::register();
//



$json = file_get_contents('php://input');
$skeletons = json_decode($json);

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
    $tableShape = $currentSlide->createTableShape($columns_count); //number of columns
    $tableShape->setHeight(50 * $rows_count);
    $tableShape->setOffsetX((960 - $columns_count * 50) / 2);
    $tableShape->setOffsetY(200);


    foreach ($rows as $i => $row) {
        // Add row
        $tableRow = $tableShape->createRow();
        $tableRow->setHeight(50);

        foreach ($row as $k => $col) {
            $oCell = $tableRow->nextCell();
            $oCell->setWidth(50);
            if ($col === 0){
            } else {

                $oCell->getFill()->setFillType(Fill::FILL_SOLID)
                    ->setStartColor(new Color('FFEEEEEE'))
                    ->setEndColor(new Color('FFEEEEEE'));
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
    setCellsBorders($tableShape);
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

        $tableShape = $currentSlide->createTableShape($columns_count); //number of columns
        $tableShape->setHeight(50 * $rows_count);
        $tableShape->setOffsetX((960 - $columns_count * 50) / 2);
        $tableShape->setOffsetY(200);
    
    
        foreach ($rows as $i => $row) {
            // Add row
            $tableRow = $tableShape->createRow();
            $tableRow->setHeight(50);
    
            foreach ($row as $k => $col) {
                $oCell = $tableRow->nextCell();
                $oCell->setWidth(50);
                if ($col === 0) {

                    $textRun = $oCell->createTextRun(' ');
                    $textRun->getFont()
                        ->setSize(20);

                    
                } else {
                   
                    $oCell->getFill()->setFillType(Fill::FILL_SOLID)
                    ->setStartColor(new Color('FFEEEEEE'))
                    ->setEndColor(new Color('FFEEEEEE'));
                $textRun = $oCell->createTextRun($col);
                $textRun->getFont()
                    ->setSize(20);

                }

                $oCell->getActiveParagraph()
                ->getAlignment()
                ->setHorizontal(Alignment::HORIZONTAL_CENTER)
                ->setVertical(Alignment::VERTICAL_CENTER);

            }
        }

        setCellsBorders($tableShape);
    

 
}


function setCellsBorders($tableShape)
{
    $tableRows = $tableShape->getRows();
    $startColor = new Color('FFEEEEEE');

    foreach ($tableRows as $rowIndex => $row) {
        $tableCells = $row->getCells();

        foreach ($tableCells as $cellIndex => $cell) {
            $borders = new Borders();
            //if true  add white border
            $left = true;
            $top = true;
            $right = true;
            $bottom = true;

            if ($cell->getFill()->getStartColor() == $startColor) { //if the cell contains a letter no white borders
                $left = false;
                $top = false;
                $right = false;
                $bottom = false;
            }
            if (($cellIndex + 1) < count($tableCells)) { //check if we aren't on the last cell of the row so we can check the next cell
                $nextCell = $row->getCell($cellIndex + 1);

                if ($nextCell->getFill()->getStartColor() == $startColor) { //if th next cell contains a letter, no white border to the right
                    $right = false;
                }
            }
            if (($cellIndex - 1) >= 0) { //check if we have a previous cell
                $prevCell = $row->getCell($cellIndex - 1);

                if ($prevCell->getFill()->getStartColor() == $startColor) { //if th next cell contains a letter, no white border to the right
                    $left = false;
                }
            }

            if (($rowIndex + 1) < count($tableRows)) { //check if we are on the last row of the table
                $nextRow = $tableShape->getRow($rowIndex + 1);
                $nextRowCell = $nextRow->getCell($cellIndex);

                if ($nextRowCell->getFill()->getStartColor() == $startColor) { //if the cell under the current one has a letter, no white border in the bottom 
                    $bottom = false;
                }
            }
            if (($rowIndex - 1) >= 0) { //check if we have a row before
                $prevRow = $tableShape->getRow($rowIndex - 1);
                $prevRowCell = $prevRow->getCell($cellIndex);

                if ($prevRowCell->getFill()->getStartColor() == $startColor) { //if the cell over the current one has a letter, no white border in the top 
                    $top = false;
                }
            }

            if ($left) {
                $borders->getLeft()->setLineWidth(0);
            }
            if ($top) {
                $borders->getTop()->setLineWidth(0);
            }
            if ($right) {
                $borders->getRight()->setLineWidth(0);
            }
            if ($bottom) {
                $borders->getBottom()->setLineWidth(0);
            }

            $cell->setBorders($borders);
        }
    }
}




$writerPPTX = IOFactory::createWriter($presentation, 'PowerPoint2007');

$postfix = count($skeletons) > 1 ? 'N' : '1';
$filename = 'skeleton_' . $postfix . '_' . time() . '.pptx';
$writerPPTX->save('ppt/' . $filename);

echo $filename;
