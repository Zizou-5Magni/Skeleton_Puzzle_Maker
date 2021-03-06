<?php

/* 
* SkeletonPuzzleMaker makes multiple instances of the skeleton puzzle.
* If one version outperforms another (by placing more words) then use that skeleton instead.
* 0 = place first word at cell (0, 0) on the grid going across
* 1 = place first word at cell (0, 0) on the grid going down
* 2 = place first word on the last row going across at last available position
* 3 = place first word in random direction in a random cell
*/
class SkeletonPuzzleMaker
{

	// Final skeleton to be generated into a puzzle
	private $skeleton;


	public function __construct($width, $height, $words, $teluguWords = [])
	{
		require_once("Skeleton.php");

		$startTime = time();
		$endTime = $startTime + 15;
		$this->endTime = $endTime;
		$this->wordList = $words;
		$this->width = $width;
		$this->height = $height;


		// Create a puzzle
		$puzzle = new Skeleton($width, $height, $words, 0, $teluguWords);
		$this->skeleton = $puzzle;

		
		$this->skeleton->generatePuzzle();
	}
	public function getCharacters()
	{
		return $this->skeleton->getCharacters();
	}
	public function getSolution()
	{
		return $this->skeleton->getSolution();
	}


	public function getPuzzle()
	{
		return $this->skeleton->getPuzzle();
	}

	public function getPuzzleNumbers()
	{
		return $this->skeleton->getPuzzleNumbers();
	}

	public function getUnplacedWords()
	{
		return $this->skeleton->getUnplacedWords();
	}
	public function getIfTelugu()
	{
		return $this->skeleton->getIfTelugu();
	}
}
