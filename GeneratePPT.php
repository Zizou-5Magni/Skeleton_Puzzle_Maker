<?php


//Importing library elements
use PhpOffice\PhpPresentation\Shape\Drawing\Base64;
use PhpOffice\PhpPresentation\IOFactory;
use PhpOffice\PhpPresentation\PhpPresentation;
use PhpOffice\PhpPresentation\Style\Alignment;
use PhpOffice\PhpPresentation\Style\Color;


require_once 'libs/PhpPresentation/src/PhpPresentation/Autoloader.php';
\PhpOffice\PhpPresentation\Autoloader::register();
require_once 'libs/Common/src/Common/Autoloader.php';
\PhpOffice\Common\Autoloader::register();
//



$json = file_get_contents('php://input');
$data = json_decode($json);

$skeletons = $data->skeletons;
$solutions = $data->solutions;

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
    $imageShape = new Base64();
    $imageShape
        ->setName('Skeleton')
        ->setDescription('Skeleton')
        ->setData($skeleton->image)
        ->setResizeProportional(false)
        ->setHeight($skeleton->height)
        ->setWidth($skeleton->width)
        ->setOffsetX((960 - $skeleton->width) / 2)
        ->setOffsetY(200);

    $currentSlide->addShape($imageShape);
}


foreach ($solutions as $i => $solution) {
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
    $number = count($skeletons) > 1 ? $i + 1 : '';
    $textRun = $textShape->createTextRun('Solution ' . $number);
    $textRun->getFont()
        ->setBold(true)
        ->setSize(28)
        ->setColor(new Color('FF0D6EFD'));

    //Add Skeleton Image
    $imageShape = new Base64();
    $imageShape
        ->setName('Solution Skeleton')
        ->setDescription('Solution Skeleton')
        ->setData($solution->image)
        ->setResizeProportional(false)
        ->setHeight($solution->height)
        ->setWidth($solution->width)
        ->setOffsetX((960 - $solution->width) / 2)
        ->setOffsetY(200);

    $currentSlide->addShape($imageShape);
}







$writerPPTX = IOFactory::createWriter($presentation, 'PowerPoint2007');

$postfix = count($skeletons) > 1 ? 'N' : '1';
$filename = 'skeleton_' . $postfix . '_' . time() . '.pptx';
$writerPPTX->save('ppt/' . $filename);

echo $filename;
