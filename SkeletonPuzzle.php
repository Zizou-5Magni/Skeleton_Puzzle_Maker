<?php

require_once("SkeletonPuzzleMaker.php");

if ($_SERVER['REQUEST_METHOD'] != 'POST') {
	// If visiting for the first time by skipping the index page redirect them to it
	$url = "index.html";
	header("Location: " . $url);
	die();
}

// Set starting variables gotten from post
$title = $_POST["title"];
$subtitle = $_POST["subtitle"];
$wordList = $_POST["wordInput"];
$multi = $_POST["multi"];

// Set defaults if they weren't set on index page
if ($title == "" || $title == null) {
	$title = "Skeleton Puzzle";
}

if ($subtitle == "" || $subtitle == null) {
	$subtitle = "A skeleton puzzle";
}

// Create an array of words pair
$words = generateWordList($wordList);
$shuffledWords = $words;



$height = 10;
$width = 10;




$puzzles = [];
$unplacedWords = [];
// Creates a few Skeleton Puzzles and then keeps the one with the most placed words

if ($multi == 'true') {
	$start_time = time();
	while (count($shuffledWords) > 1 && (time() - $start_time) < 20) { //setting a timeout of 20 seconds

		$threeRandomWords = getThreeRandomWords($shuffledWords);

		$skeletonMaker = new SkeletonPuzzleMaker($width, $height, $threeRandomWords);

		$unplacedWords = $skeletonMaker->getUnplacedWords();

		$telugu = $skeletonMaker->getIfTelugu();

		if ($telugu) {

			if (count($unplacedWords) <= 1) {

				$puzzles[] = $skeletonMaker;
				$shuffledWords = array_merge($shuffledWords, $skeletonMaker->getUnplacedWords());
			} else {
				$shuffledWords = array_merge($shuffledWords, $threeRandomWords);
			}
		} else {

			if (empty($unplacedWords)) {

				$puzzles[] = $skeletonMaker;
			} else {
				$shuffledWords = array_merge($shuffledWords, $threeRandomWords);
			}
		}
	}
	$unplacedWords = $shuffledWords;
	//$shuffledWord contains the words that could not be placed in any puzzle
} else {
	$threeRandomWords = getThreeRandomWords($shuffledWords);
	$skeletonMaker = new SkeletonPuzzleMaker($width, $height, $threeRandomWords);
	$puzzles[] = $skeletonMaker;

	$unplacedWords = $skeletonMaker->getUnplacedWords();
}


// Generates the word list for words paired with hints
// Splits word from hint by taking the sides from the first comma, then trims extra space from each
// Returns array in format word[i][0] = word, word[i][1] = hint
function generateWordList($wordInput)
{

	$words = [];

	$lines = explode("\n", $wordInput);
	foreach ($lines as $line) {

		$word = trim($line);

		if (!(empty($word))) {
			array_push($words, $word);
		}
	}
	return $words;
}

function getThreeRandomWords(&$wordList)
{
	shuffle($wordList);
	$elements = [];
	for ($i = 0; $i < 3; $i++) {
		if (!empty($wordList)) {
			array_push($elements, array_pop($wordList));
		} else {
			break;
		}
	}

	return $elements;
}
?>
<!DOCTYPE html PUBLIC '-//W3C//DTD XHTML 1.0 Transitional//EN''http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd'>
<html xmlns='http://www.w3.org/1999/xhtml' xml:lang='en' lang='en'>

<head>

	<!-- Bootstrap CSS -->
	<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-EVSTQN3/azprG1Anm3QDgpJLIm9Nao0Yz1ztcQTwFspd3yD65VohhpuuCOmLASjC" crossorigin="anonymous" />

	<!-- Spectrum -->
	<link rel="stylesheet" type="text/css" href="css/spectrum.css">
	<link rel="stylesheet" href="css/styles.css">


	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale = 1">

	<title>Skeleton Puzzle</title>

	<link rel="stylesheet" type="text/css" href="css/styles.css">
</head>

<body>
	<div class="container">
		<div class="row">
			<div class="col-12">
			<div class="mx-4 py-3 text-center">
					<button class="btn btn-primary" id="generatePPT">Generate PPT</button>
				</div>

				<div class="mt-4 py-3 text-center blue">
					<h4>Options</h4>
				</div>
				<div class="border-blue p-3 mb-3">

					<div id="options-container">
						<div class="mb-2"> <input type="checkbox" class="showBlankSquaresCheckbox" name="showBlankSquares" onchange="blankSquareCheckboxChange()"> Show blank squares</div>
						<div class="colorpicker-container">
							<label>Blank Square Color</label>
							<input type="text" class='blankSquareColor' name='blankSquareColor' value='#FFFFFF' />
						</div>
						<div class="colorpicker-container">
							<label>Letter Square Color</label>
							<input type="text" class='letterSquareColor' name='letterSquareColor' value='#EEEEEE' />
						</div>
						<div class="colorpicker-container">
							<label>Letter Color</label>
							<input type="text" class='letterColor' name='letterColor' value='#000000' />
						</div>
						<div class="colorpicker-container">
							<label>Line Color</label>
							<input type="text" class='lineColor' name='lineColor' value='#000000' />
						</div>
					</div>

				</div>
				<?php foreach ($puzzles as $puzzle) : ?>


					<div class="card border-primary mb-3">
						<div class="card-header bg-primary text-center py-4">
							<h3>Skeleton Puzzle</h3>
						</div>
						<div class="card-body text-center">
							<h4>Title</h4>
							<h5 class="mb-5">Subtitle</h5>
							<table class="skeleton mx-auto">
								<?php
								// Print the skeleton puzzle
								foreach ($puzzle->getPuzzle() as $key => $row) {
									echo '<tr>';
									foreach ($row as $k => $val) {
										if ($val != "0") {
											echo '<td class="filled">&nbsp;&nbsp;&nbsp;&nbsp;</td>
													';
										} else {
											echo '<td class="unfilled"> &nbsp;&nbsp;&nbsp;&nbsp; </td>
													';
										}
									}
									echo '</tr>';
								}
								?>
							</table>
							<!-- Character list -->
							<div class="p-3 mt-5 border-blue">
								<h4>Characters</h4>
								<?php
								foreach ($puzzle->getCharacters() as $char) {
									echo (' ' . $char . ' ');
								}
								?>
							</div>

							<!-- Solution -->
							<div class="accordion mt-3">
								<div class="accordion-item border-blue">

									<span class="accordion-button text-center collapsed" data-bs-toggle="collapse" data-bs-target="#collapseSolution" aria-expanded="false">
										<h4>Solution</h4>
									</span>

									<div id="collapseSolution" class="accordion-collapse collapse">
										<div class="accordion-body">
											<table class="skeleton-solution mx-auto">
												<?php
												// Display the solution
												foreach ($puzzle->getSolution() as $key => $row) {
													echo '<tr>';
													foreach ($row as $k => $val) {
														if ($val != "0") {
															echo '<td class="filled">' . $val . '</td>
														';
														} else {
															echo '<td class="unfilled"> &nbsp;&nbsp;&nbsp;&nbsp; </td>
														';
														}
													}
													echo '</tr>';
												}
												?>
											</table>


											<div class="mt-4 py-3 text-center blue">
												<h4>Words</h4>
											</div>
											<div class="border-blue p-3">
												<div class="row">
													<div class="col-6 border-right">
														<h5>Across</h5>
														<div class="text-start mt-3">
															<?php
															// Display the solution words going across
															foreach ($puzzle->getPuzzleNumbers() as $placedLocation) {
																if ($placedLocation[3] == "right") {

																	echo ("<span class='d-block'><b>" . $placedLocation[5] . "-  </b>" . $placedLocation[0] . "</span>");
																}
															}
															?>
														</div>
													</div>
													<div class="col-6 border-left">
														<h5>Down</h5>
														<div class="text-start mt-3">
															<?php
															// Display the solution words going across
															foreach ($puzzle->getPuzzleNumbers() as $placedLocation) {
																if ($placedLocation[3] == "down") {

																	echo ("<span class='d-block'><b>" . $placedLocation[5] . "-  </b>" . $placedLocation[0] . "</span>");
																}
															}
															?>
														</div>
													</div>
												</div>
											</div>

										</div>
									</div>
								</div>

							</div>


						</div>
					</div>
				<?php endforeach ?>
				<!-- Unplaced Words -->
				<?php if (!empty($unplacedWords)) : ?>
					<div class="mt-4 py-3 text-center red">
						<h4>Unplaced Words</h4>
					</div>
					<div class="border-red p-3">
						<div class="row text-center">
							<?php
							foreach ($unplacedWords as $word) {
								echo '<span class="d-block">' . $word . '</span>';
							} ?>
						</div>
					</div>
				<?php endif ?>
			</div>
		</div>
	</div>

	<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js" integrity="sha384-MrcW6ZMFYlzcLA8Nl+NtUVF0sA7MsXsP1UyJoMp4YLEuNSfAP+JcXn/tWtIaxVXM" crossorigin="anonymous"></script>
	<script src="https://code.jquery.com/jquery-3.6.0.js" integrity="sha256-H+K7U5CnXl1h5ywQfKtSj8PCmoN9aaq30gDh27Xc0jk=" crossorigin="anonymous"></script>
	<script type="text/javascript" src="js/spectrum.js"></script>
	<script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.3.2/html2canvas.min.js" integrity="sha512-tVYBzEItJit9HXaWTPo8vveXlkK62LbA+wez9IgzjTmFNLMBO1BEYladBw2wnM3YURZSMUyhayPCoLtjGh84NQ==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>						
	<script src="js/script.js"></script>
</body>

</html>