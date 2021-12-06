<?php

/* 
	 * Class for generating a skeleton puzzle
	 * Takes in a list of words, grid width and height, and placement type to generate a solution.
	 * Based on the placement type the first word will get placed on a location.
	 * Algorithm will then attempt to keep placing words on the grid that fit until no more words can be placed.
	 * After a solution is generated a puzzle can be created by calling the generatePuzzle method.
	 * The score variable keeps track of how many words were placed so that the solution can be compared to other generated solutions.
	 * Also creates a fillin puzzle list where word placed locations are ordered by word length with length recorded.
	 */
class Skeleton
{
	private $wordList = [];
	private $unplacedWordList = [];
	private $placedWords = [];
	private $puzzleNumbers = [];
	private $fillinList = [];

	private $width;
	private $height;
	private $score = 0;

	private $solution = [];
	private $puzzle = [];
	private $placedLetters = [];
	private $characterList = [];	//add a character list 
	
	// private $wordProcessor;

	//Setting Telugu to false and creating a Telugu list
	private $telugu = false;
	private $teluguWords = [];

	/*
	 * Creates a solution upon creation.
	 * A call to generatePuzzle() must be made to generate a puzzle based on the solution.
	*/

	//Constructor 
	public function __construct($width, $height, $wordList, $type, $teluguWords = [])
	{
		// Set starting values

		$this->width = $width;
		$this->height = $height;
		$this->firstWordSetting = $type;
		$this->wordList = $wordList;
		$this->teluguWords = $teluguWords;

		if ($teluguWords != []) {
			$this->telugu = true;
		} else {
			$this->telugu = false;
		}

		$this->shuffleCharacters();
		// Adjust grid size based off longest word in case grid is too small
		$this->adjustGridSize();

		// Create an empty board
		$this->generateBoard($this->width, $this->height);

		// Create the solution
		$this->generateSolution();
	}



	function getLongestStringInArray($array)
	{
		$mapping = array_combine($array, array_map('strlen', $array));
		return array_keys($mapping, max($mapping));
	}
	/*
	 * Adjust gridsize based off the longest word
	 */


	private function adjustGridSize()
	{
		$maxlen = 0;
		//get the longest word length from the list of words given
		if ($this->telugu) {
			$lenght = 0;
			foreach ($this->wordList as $word => $intersections) {
				$maxlen = $lenght < count($this->teluguWords[$word]) ? count($this->teluguWords[$word]) : $lenght;
			}
		} else {
			$maxlen = max(array_map('strlen', array_keys($this->wordList)));
		}
		$maxlen = $maxlen * 3;
		if ($maxlen > $this->width) {
			$this->width = $maxlen;
		}

		if ($maxlen > $this->height) {
			$this->height = $maxlen;
		}
	}


	private function shuffleCharacters()
	{
		$characterList = [];


		if ($this->telugu) {

			foreach ($this->wordList as $word => $intersections) {
				$characterList = array_merge($characterList, $this->teluguWords[$word]);
			}
		} else {

			foreach ($this->wordList as $word => $intersections) {
				$chars = str_split($word);
				foreach ($chars as $char) {
					array_push($characterList, $char);
				}
			}
		}


		shuffle($characterList);
		//$characterList = array_unique($characterList);

		$this->characterList = $characterList;
	}

	/*
	 * Generates an empty board based off width and height sizes
	 */
	private function generateBoard($width, $height)
	{
		$this->solution = array_fill(0, $height, array_fill(0, $width, 0));
	}

	/*
	 * Creates a solution for the skeleton puzzle.
	 * First places the first word (the one with most potential) and then continues to place the others.
	 * Scans through each placed letter on the grid trying to see if it has an intersection with current word.
	 * If it does, determine the starting cell for going down/across and see if the word can be placed.
	 * After a whole loop through the remaining words without placing anything then end placement.
	 */
	private function generateSolution()
	{


		// Create a temporary word list for solution generation
		$words = $this->wordList;
		$keys = array_keys(($this->wordList));
		$firstWord = '';
		$firstWordIntersections = [];

		foreach ($words as $word => $intersections) {
			$wordIntersections = [];
			foreach ($intersections as $interWord => $char) {
				if (in_array($interWord, $keys)) {
					$wordIntersections[$interWord] = $char;
				}
			}
			$words[$word] = $wordIntersections;
		}

		foreach ($words as $word => $intersections) {
			if (count($intersections) == 2) {
				$wordKeys = array_keys($intersections);
				foreach ($intersections[$wordKeys[0]] as $char) {
					foreach ($intersections[$wordKeys[1]] as $comparingChar) {
						if ($char != $comparingChar) {
							$words[$word][$wordKeys[0]] =  $char;
							$words[$word][$wordKeys[1]] = $comparingChar;
							$firstWord = $word;
							$firstWordIntersections = $words[$firstWord];
							break 2;
						}
					}
				}
			}
		}


		// $directions = ['right', 'down'];
		$col = 0;
		$row = 0;

		// $randomIndex = rand(0, 1);
		// $dir = $directions[$randomIndex];

		$dir = 'down';
		if ($dir == 'right') {
			foreach ($firstWordIntersections as $word => $char) {
				if ($this->telugu) {
					$charPosition = array_search($char, $this->teluguWords[$word]);
				} else {
					$charPosition = strpos($word, $char);
				}

				$row = $row < $charPosition ? $charPosition : $row;
			}
		} else {
			foreach ($firstWordIntersections as $word => $char) {
				if ($this->telugu) {
					$charPosition = array_search($char, $this->teluguWords[$word]);
				} else {
					$charPosition = strpos($word, $char);
				}
				$col = $col < $charPosition ? $charPosition : $col;
			}
		}
		$placeLocation = [$row, $col, $dir];

		$this->placeWord($firstWord, $placeLocation);

		foreach ($keys as $word) {
			if ($word != $firstWord) {


				$char = $words[$firstWord][$word];
				if ($dir == 'right') {
					if ($this->telugu) {
						$wordCol = array_search($char, $this->teluguWords[$firstWord]);
						$wordRow = $row - array_search($char, $this->teluguWords[$word]);
					} else {
						$wordCol = strpos($firstWord, $char);
						$wordRow = $row - strpos($word, $char);
					}

					$wordDir = 'down';
				} else {
					if ($this->telugu) {
						$wordCol = $col - array_search($char, $this->teluguWords[$word]);
						$wordRow = array_search($char, $this->teluguWords[$firstWord]);
					} else {
						$wordCol = $col - strpos($word, $char);
						$wordRow = strpos($firstWord, $char);
					}
					$wordDir = 'right';
				}

				$this->placeWord($word, [$wordRow, $wordCol, $wordDir]);
			}
		}



		$this->unplacedWordList = [];
	}



	/*
	 * Creates a puzzle based off the generated solution.
	 * Must be called after the initial creation of the Skeleton class.
	 * Creates a list of across and down words with their hints and assigns each a number.
	 * Numbers are then placed on the puzzle grid.
	 * If two words start at the same cell, but different directions, then they will both have the same number.
	 */
	public function generatePuzzle()
	{
		// Copy list of across and down words
		// $listAcross = $this->placedWordListAcross;
		// $listDown = $this->placedWordListDown;

		$wordList = $this->placedWords;

		// Sort the words by their placement on the grid
		// Words on higher rows and lowest columns take priority
		// $listAcross = $this->sortPlacedWords($listAcross);
		// $listDown = $this->sortPlacedWords($listDown);
		$wordList = $this->sortPlacedWords($wordList);

		// Start looping for each word going across and assign it a number
		// If that word's starting position also contains a word going down then assign the number also to the down word.
		// When assigning down words do not repeate a number already used for a down word assigned during across loop.
		$numberCount = 1;


		for ($i = 0, $size = count($wordList); $i < $size; $i++) {
			$currentWord = $wordList[$i];

			if (isset($currentWord[5])) {
				continue;
			}

			if ($i < (count($wordList)) - 1) {
				$nextWord = $wordList[$i + 1];

				if ($currentWord[1] == $nextWord[1] && $currentWord[2] == $nextWord[2]) {
					$wordList[$i + 1][5] = $numberCount;
				}
			}

			$wordList[$i][5] = $numberCount;
			$numberCount++;
		}
		$this->puzzleNumbers = $wordList;

		// Create the puzzle board based off assigned puzzle numbers
		$this->generatePuzzleBoard();

		// Reduce the grid size for empty grid rows/columns at the beginning/end of solution
		// Must be done last since placements rely on full grid
		$this->reduceGridSize();
	}

	/*
	 * Sort word list based off the location of the placed word.
	 * Priority should be row then column.
	 * Example: Word in cell (0, 0) should be sorted to top, while (width, length) should be at the bottom
	 * Keeps puzzle numbers in order based off their placement on the grid.
	 * Returns the sorted list.
	 */
	private function sortPlacedWords($placedWordList)
	{

		// Custom sort to sort the words
		usort($placedWordList, function ($a, $b) {
			// row a > row b
			if ($a[1] > $b[1]) {
				return 1;
			}
			// row a == row b
			else if ($a[1] == $b[1]) {
				// col a > col b
				if ($a[2] > $b[2]) {
					return 1;
				}
				// col a == col b
				else if ($a[2] == $b[2]) {
					if ($b[3] == "right") {
						return 1;
					} else {
						return -1;
					}
				}
				// col a < col b
				else {
					return -1;
				}
			}
			// row a < row b
			else {
				return -1;
			}
		});

		return $placedWordList;
	}


	/*
	 * Generates a puzzle board based off the puzzle numbers created in generatePuzzle() method.
	 * First converts each letter in the placed grid to a blank value.
	 * Then places the puzzle numbers at each starting location.
	 */
	private function generatePuzzleBoard()
	{
		$this->puzzle = $this->solution;

		$i = 0;
		$j = 0;

		foreach ($this->puzzle as &$row) {
			foreach ($row as &$col) {
				if ($col != "0") {
					$col = " ";
				}
			}
		}

		foreach ($this->puzzleNumbers as $placedLocation) {
			$this->puzzle[$placedLocation[1]][$placedLocation[2]] = $placedLocation[5];
		}
	}


	/*
	 * Places word in passed in placeLocation: [0] row, [1] col, [2] dir.
	 * Add each placed letter to the placedLetters list.
	 * Add each placed word into either the placed lists for across or down words.
	 * Increment score when a word is added.
	 */
	private function placeWord($word, $placeLocation)
	{
		$row = $placeLocation[0];
		$col = $placeLocation[1];
		$dir = $placeLocation[2];

		// Create placedWord array to be added to placedWord list
		$length = $this->getWordLength($word);


		$letters = $this->splitWord($word);

		$placedWord = [];
		$placedWord[0] = $word;
		$placedWord[1] = $row;
		$placedWord[2] = $col;
		$placedWord[4] = $length;


		$addedLetter = [];

		// For each letter place it on the grid and then add it to placed letters array
		if ($dir == "right") {
			for ($i = 0; $i < $length; $i++) {
				if ($this->solution[$row][$col + $i] == "0") {
					$addedLetter[0] = $letters[$i];
					$addedLetter[1] = $row;
					$addedLetter[2] = $col + $i;

					array_push($this->placedLetters, $addedLetter);
				}
				$this->solution[$row][$col + $i] = $letters[$i];
			}

			$placedWord[3] = "right";

			array_push($this->placedWords, $placedWord);
		} else {
			for ($i = 0; $i < $length; $i++) {
				if ($this->solution[$row + $i][$col] == "0") {
					$addedLetter[0] = $letters[$i];
					$addedLetter[1] = $row + $i;
					$addedLetter[2] = $col;

					array_push($this->placedLetters, $addedLetter);
				}
				$this->solution[$row + $i][$col] = $letters[$i];
			}
			$placedWord[3] = "down";
			array_push($this->placedWords, $placedWord);
		}

		// Increment score to keep track of how many words have been placed on the grid
		$this->score++;
	}

	/*
	 * Removes any columns or rows that appear at the beginning/end of the generated puzzle and solution
	 * This removes a lot of empty space from large puzzles
	 * Must be called after puzzle has been completed since many values are based on absolute positioning on the grid
	 * Loops go from top/bottom to find empty rows/columns.  If empty, then unset it. If not empty than break loop
	 * since there should be no more empties that direction.
	 */
	private function reduceGridSize()
	{


		// Delete columns first - otherwise issue happens where columns don't get deleted
		// Fix would be in the count(array_unique()) line, but was easier to just remove columns first.

		// Delete blank columns on right side
		for ($i = $this->width - 1; $i >= 0; $i--) {
			$column = $this->getColumn($i);

			if ((count(array_unique($column)) == 1) && array_values(array_unique($this->solution[$i]))[0] == "0") {
				$this->removeColumn($i);
			} else {
				break;
			}
		}

		// Delete blank columns on left side
		// Get reference to column size first since values will be unset
		$columnCount = count($this->solution[0]);

		for ($i = 0; $i < $columnCount; $i++) {
			$column = $this->getColumn($i);

			if ((count(array_unique($column)) == 1) && array_values(array_unique($this->solution[$i]))[0] == "0") {
				$this->removeColumn($i);
			} else {
				break;
			}
		}

		// Delete the blank rows on top - only delete if whole row is blank (0)
		for ($i = 0; $i <= $this->height - 1; $i++) {
			if ((count(array_unique($this->solution[$i])) == 1) && array_values(array_unique($this->solution[$i]))[0] == "0") {
				$this->removeRow($i);

				// De-increment since array keys are reset
				$i--;
			} else {
				break;
			}
		}

		// Delete the blank rows on bottom - only delete if whole row is blank (0)
		for ($i = count($this->solution) - 1; $i >= 0; $i--) {

			if ((count(array_unique($this->solution[$i])) == 1) && array_values(array_unique($this->solution[$i]))[0] == "0") {
				$this->removeRow($i);
			} else {
				break;
			}
		}
	}


	private function getColumn($col)
	{
		$column = [];
		for ($i = 0; $i < count($this->solution); $i++) {
			array_push($column, $this->solution[$i][$col]);
		}

		return $column;
	}

	/*
	 * Removes the row by unsetting it and then re-indexes
	 */
	private function removeRow($i)
	{
		unset($this->solution[$i]);
		unset($this->puzzle[$i]);
		$this->solution = array_values($this->solution);
		$this->puzzle = array_values($this->puzzle);
	}

	/*
	 * Removes the column from the input position by unsetting all of that column's values in each row and then re-indexes
	 */
	private function removeColumn($col)
	{
		for ($i = 0; $i < count($this->solution); $i++) {
			unset($this->solution[$i][$col]);
			unset($this->puzzle[$i][$col]);
			$this->solution = array_values($this->solution);
			$this->puzzle = array_values($this->puzzle);
		}
	}



	/*** Word Processor Functions ***/
	private function getWordLength($word)
	{
		if ($this->telugu) {
			return count($this->teluguWords[$word]);
		} else {
			return strlen($word);
		}
	}

	private function splitWord($word)
	{
		$splittedWord = "";
		if ($this->telugu) {
			$splittedWord = $this->teluguWords[$word];
		} else {
			$splittedWord = str_split($word);
		}

		return $splittedWord;
	}

	/*** Getter functions ***/

	public function getSolution()
	{
		return $this->solution;
	}

	public function getPuzzle()
	{
		return $this->puzzle;
	}

	public function getUnplacedWords()
	{
		return $this->unplacedWordList;
	}

	public function getPuzzleNumbers()
	{
		return $this->puzzleNumbers;
	}

	public function getScore()
	{
		return $this->score;
	}

	public function getFillInHints()
	{
		return $this->fillinList;
	}
	public function getCharacters()
	{
		return $this->characterList;
	}

	public function getIfTelugu()
	{
		return $this->telugu;
	}
}
