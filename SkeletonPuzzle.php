<?php
// set_time_limit(500);
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
$words = array_unique($words);
shuffle($words);
$height = 10;
$width = 10;

$puzzles = [];
$unplacedWords = [];

$isTelugu = isTelugu($words[0]);
$teluguDictionary = [];
if ($isTelugu) {
	$teluguDictionary = getTeluguCharacters($words); //contains for each telugu word, the list of the characters of the word
}

$intersectionCharList = [];
$intersectionList = generateIntersectionList($words, $isTelugu, $teluguDictionary);

sortWordList($intersectionList);

if ($multi == 'true') { //multiple skeletons generation
	$threeWords = getThreeWords($intersectionList, $isTelugu, $teluguDictionary);

	while (!empty($threeWords)) {
		$skeletonMaker = new SkeletonPuzzleMaker($width, $height, $threeWords, $teluguDictionary);
		$puzzles[] = $skeletonMaker;
		$threeWords = getThreeWords($intersectionList, $isTelugu, $teluguDictionary);
	}
} else { //single skeleton generation
	$threeWords = getThreeWords($intersectionList, $isTelugu, $teluguDictionary);
	if (!empty($threeWords)) {
		$skeletonMaker = new SkeletonPuzzleMaker($width, $height, $threeWords, $teluguDictionary);
		$puzzles[] = $skeletonMaker;
	}
}
$unplacedWords = $intersectionList;


function generateIntersectionList($words, $isTelugu, $teluguDictionary)
{
	$intersectionList = [];
	//the intersectionList will be like: 'word'->['matchedWord,'commonLetter'];

	foreach ($words as $word) {
		$intersectionList[$word] = [];
	}


	for ($i = 0; $i < count($words); $i++) {  //for every word
		$currentWordCharacters = [];
		if ($isTelugu) {
			$currentWordCharacters = $teluguDictionary[$words[$i]];
		} else {
			$currentWordCharacters = str_split($words[$i]);
		}

		$currentWord = $words[$i];
		foreach ($currentWordCharacters as $character) { //for every character in the word
			for ($j = 0; $j < count($words); $j++) { //for each word in the list, add it to the list of words the current word has an intersection with
				$comparingWord = $words[$j];

				if ($i != $j) { //exclude the current selected word
					if ($isTelugu) {
						if (in_array($character, $teluguDictionary[$comparingWord])) { //if we find a character match, add it to the list

							$intersectionList[$currentWord][$comparingWord][] = $character;
						}
					} else {
						if (strpos($comparingWord, $character) !== false) { //if we find a character match, add it to the list

							$intersectionList[$currentWord][$comparingWord][] = $character;
						}
					}
				}
			}
		}
	}

	return $intersectionList;
}

function sortWordList(&$intersectionList)
{
	arsort($intersectionList);
}
function getThreeWords(&$intersectionList, $isTelugu, $teluguDictionary)
{

	$words = array_keys($intersectionList);
	$resultWords = [];
	foreach ($intersectionList as $word => $intersections) {
		$resultWords = [];
		$firstWord = $word;
		$resultWords[$word] = $intersections;
		if (count($intersections) > 0) {
			foreach ($intersections as $word => $char) {
				$secondWord = $word;
				$secondWordIntersections = $intersectionList[$secondWord];


				$excludeSameCharacter = true;

				foreach ($intersectionList[$firstWord][$secondWord] as $character) {
					if ($isTelugu) {
						$characters = array_merge($teluguDictionary[$firstWord], $teluguDictionary[$secondWord]);
					} else {
						//merge first and second word characters, if the character in common exists only two times (one per word) then the third word must have a different character
						$characters = $firstWord . $secondWord;
						if (substr_count($characters, $character) > 2) {
							$excludeSameCharacter = false;
							break;
						}
					}
				}

				foreach ($secondWordIntersections as $word => $char) {

					if ($excludeSameCharacter) {
						if (
							$word != $firstWord
							&& $intersectionList[$firstWord][$secondWord] != $intersectionList[$secondWord][$word]
						) {
							$thirdWord = $word;
							$resultWords[$secondWord] = $intersectionList[$secondWord];
							$resultWords[$thirdWord] = $intersectionList[$thirdWord];

							//remove all the words used from the original list
							unset($intersectionList[$firstWord]);
							unset($intersectionList[$secondWord]);
							unset($intersectionList[$thirdWord]);

							$words = array_keys($intersectionList);

							foreach ($words as $word) {

								unset($intersectionList[$word][$firstWord]);
								unset($intersectionList[$word][$secondWord]);
								unset($intersectionList[$word][$thirdWord]);
							}
							return $resultWords;
						}
					} else {
						if ($word != $firstWord) {
							$thirdWord = $word;
							$resultWords[$secondWord] = $intersectionList[$secondWord];
							$resultWords[$thirdWord] = $intersectionList[$thirdWord];

							//remove all the words used from the original list
							unset($intersectionList[$firstWord]);
							unset($intersectionList[$secondWord]);
							unset($intersectionList[$thirdWord]);

							$words = array_keys($intersectionList);

							foreach ($words as $word) {

								unset($intersectionList[$word][$firstWord]);
								unset($intersectionList[$word][$secondWord]);
								unset($intersectionList[$word][$thirdWord]);
							}
							return $resultWords;
						}
					}
				}
			}
		}
	}
	return [];
}

function getTeluguCharacters($wordList)
{
	$joinedString = join(',', $wordList);

	$strings = [];
	$stringLength = strlen($joinedString);
	if ($stringLength > 2500) {
		$count = intdiv($stringLength, 2500);
		$offset = 0;
		for ($i = 0; $i <= $count; $i++) {
			if (strlen($joinedString) > $offset + 2500) {
				$splitPos = strpos($joinedString, ',', $offset + 2500);
				if ($splitPos) {
					$strings[$i] = substr($joinedString, $offset, ($splitPos) - $offset);
					$offset = $splitPos;
				} else {
					$strings[$i] = substr($joinedString, $offset);
					break;
				}
			} else {
				$strings[$i] = substr($joinedString, $offset);
				break;
			}
		}
	} else {
		$strings[] = $joinedString;
	}


	$dictionary = [];
	$wordIndex = 0;
	foreach ($strings as $string) {
		$url = 'https://indic-wp.thisisjava.com/api/getLogicalChars.php';

		// http_build_query builds the query from an array
		$query_array = array(
			'string' => $string,
			'language' => 'Telugu'
		);

		$query = http_build_query($query_array);

		$response = file_get_contents($url . '?' . $query);
		$response = preg_split('@(?={)@', $response)[1]; //remove weird characters from the start of the response, otherwise we can't decode the response.
		$response = json_decode($response); //convert the JSON response into an object
		$splittedWords = $response->data;

		foreach ($splittedWords as $item) {
			if ($item != ',') {
				$dictionary[$wordList[$wordIndex]][] = $item;
			} else {
				$wordIndex++;
			}
		}
	}


	return $dictionary;
}

function isTelugu($word)
{
	if (preg_match("/^[A-Za-z]+$/", $word)) { //if the word is  alphabetical then we assume it's not Telugu
		return false;
	} else {
		return true;
	}
}

// Generates the word list from input words 
function generateWordList($wordInput)
{

	$words = [];

	$lines = explode("\n", $wordInput);
	foreach ($lines as $line) {

		$word = trim($line);
		$word = str_replace(' ', '', $word);
		$word = str_replace(';', '', $word);
		$word = str_replace(',', '', $word);
		$word = str_replace('.', '', $word);
		$word = str_replace('-', '', $word);
		if (!(empty($word))) {
			array_push($words, $word);
		}
	}
	return $words;
}


$skeletons = [];
foreach ($puzzles as $puzzle) {
	$skeleton = new stdClass();
	$skeleton->puzzle = $puzzle->getPuzzle();
	$skeleton->solution = $puzzle->getSolution();
	$skeleton->characters = $puzzle->getCharacters();
	array_push($skeletons, $skeleton);
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
							foreach ($unplacedWords as $word => $intersections) {
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

	<script>
		// save all the skeleton data to a js variable to send to the server
		const data = <?= json_encode($skeletons);  ?>
	</script>
	<script src="js/script.js"></script>
</body>

</html>