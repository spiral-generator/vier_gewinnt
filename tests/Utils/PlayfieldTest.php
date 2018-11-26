<?php
namespace App\Tests\Utils;

use App\Utils\Playfield;
use PHPUnit\Framework\TestCase;

/**
 * Unit Tests für die Playfield-Klasse
 */
class PlayfieldTest extends TestCase {
    private $testPlayer     = 1,
            $otherPlayer    = 2,
            
            $randomSeed     = 1;        // seed für mt_rand() - auf diese Weise erhalten wir immer dieselben "Zufalls"-Zahlen
  
    /**
     * Test der Funktion zum Einsetzen eines neuen Spielsteins
     */
    public function testInsertToken(){
        mt_srand($this->randomSeed);
        
        for($run = 0; $run <= 9; $run++){
            $numRows = mt_rand(2, 12);
            $numCols = mt_rand($numRows, 12);
            
            $maxCol = $numCols - 1;
            $maxRow = $numRows - 1;
        
            $playfield = new Playfield($numCols, $numRows, 4);
            
            for($col = 0; $col <= $maxCol; $col++){
                for($expected = $maxRow; $expected >= -1; $expected--){
                    $row = $playfield->insertToken($col, 1);
                    $this->assertEquals($expected, $row);
                }
            }
        }
    }
    
    /**
     * Test, ob eine Kette in Oben-Unten-Richtung erkannt wird (wird von testDetectWin() aufgerufen)
     *
     * @param int   $numCols    Anzahl Spalten des zu testenden Spielfeldes
     * @param int   $numRows    Anzahl Reihen des zu testenden Spielfeldes
     * @param int   $gewinnt    Länge der zum Sieg benötigten Kette
     */
    private function detectWinUpDown($numCols, $numRows, $gewinnt){
        $playfield  = new Playfield($numCols, $numRows, $gewinnt);
        
        for($token = 0; $token < $gewinnt; $token++){
            $lastRow = $playfield->insertToken(0, $this->testPlayer);
        }
        
        $this->assertTrue(
            $playfield->detectWin(0, $lastRow, $this->testPlayer)
        );
    }
    
    /**
     * Test, ob eine Kette in Diagonalrichtung erkannt wird (wird von testDetectWin() aufgerufen)
     *
     * @param int   $numCols    Anzahl Spalten des zu testenden Spielfeldes
     * @param int   $numRows    Anzahl Reihen des zu testenden Spielfeldes
     * @param int   $gewinnt    Länge der zum Sieg benötigten Kette
     * @param int   $direction  Positiv: links unten nach rechts oben, negativ: links oben nach rechts unten
     */
    private function detectWinDiagonally($numCols, $numRows, $gewinnt, $direction){
        if($direction == 0){
            return false;
        }
        
        $playfield = new Playfield($numCols, $numRows, $gewinnt);
        for($column = 0; $column < $gewinnt; $column++){
            if($direction > 0){
                for($token = 0; $token < $column; $token++){
                    $playfield->insertToken($column, $this->otherPlayer);
                }
            } else {
                for($token = $column - 1; $token >= 0; $token--){
                    $playfield->insertToken($column, $this->otherPlayer);
                }            
            }
            
            $lastRow = $playfield->insertToken($column, $this->testPlayer);
            $lastColumn = $column;
        }            
        
        $this->assertTrue(
            $playfield->detectWin($lastColumn, $lastRow, $this->testPlayer)
        );        
    }
    
    /**
     * Test, ob eine Kette in Links-Rechts-Richtung erkannt wird (wird von testDetectWin() aufgerufen)
     *
     * @param int   $numCols    Anzahl Spalten des zu testenden Spielfeldes
     * @param int   $numRows    Anzahl Reihen des zu testenden Spielfeldes
     * @param int   $gewinnt    Länge der zum Sieg benötigten Kette
     */    
    private function detectWinLeftRight($numCols, $numRows, $gewinnt){
        $playfield = new Playfield($numCols, $numRows, $gewinnt);
        for($column = 0; $column < $gewinnt; $column++){
            $lastRow = $playfield->insertToken($column, $this->testPlayer);
            $lastColumn = $column;
        }
        
        $this->assertTrue(
            $playfield->detectWin($lastColumn, $lastRow, $this->testPlayer)
        );         
    }
    
    /**
     * Test der Funktion zum Erkennen, ob eine Kette von Spielsteinen mit der zum Sieg notwendigen Länge gebildet wurde
     */
    public function testDetectWin(){
        mt_srand($this->randomSeed);

        for($run = 0; $run <= 9; $run++){
            $numRows = mt_rand(2, 12);
            $numCols = mt_rand($numRows, 12); 
                
            for($gewinnt = 2; $gewinnt <= $numRows - 1; $gewinnt++){  
                $this->detectWinUpDown($numCols, $numRows, $gewinnt);
                $this->detectWinLeftRight($numCols, $numRows, $gewinnt);
                $this->detectWinDiagonally($numCols, $numRows, $gewinnt, -1);
                $this->detectWinDiagonally($numCols, $numRows, $gewinnt, 1);
            }
        }
    }
    
    /**
     * Test, dass eine Kette von nicht ausreichender Länge nicht fälschlich als ausreichend erkannt wird
     */
    public function testNotDetectWin_TooFewTokens(){
        $playfield = new Playfield(7, 6, 4);
        
        foreach([2, 5, 2, 4, 5, 6, 6, 6] as $col){
            $lastCol = $col;
            $lastRow = $playfield->insertToken($col, $this->testPlayer);            
        }
        
        $this->assertFalse(
            $playfield->detectWin($lastCol, $lastRow, $this->testPlayer)
        );
    }
    
    /**
     * Test, dass eine Kette von ausreichender Länge, die aber aus Spielsteinen des Gegners besteht, nicht fälschlich als Sieg erkannt wird
     */
    public function testNotDetectWin_WrongPlayer(){
        $numCols = 7;
        $numRows = 6;
        
        for($gewinnt = 2; $gewinnt <= $numRows - 1; $gewinnt++){  
            $playfield = new Playfield($numCols, $numRows, $gewinnt);
            
            for($column = 0; $column < $gewinnt; $column++){
                $lastRow = $playfield->insertToken($column, $this->testPlayer);
                $lastColumn = $column;
            }
            
            $this->assertFalse(
                $playfield->detectWin($lastColumn, $lastRow, $this->otherPlayer)
            );
        }
    }
    
    /**
     * Test, ob ein Unentschieden korrekt erkannt wird
     */
    public function testDetectDraw(){
        mt_srand($this->randomSeed);
        
        for($run = 0; $run <= 9; $run++){
            $numRows    = mt_rand(2, 12);
            $numCols    = mt_rand($numRows, 12);
            $gewinnt    = mt_rand(2, $numCols - 1);
            $players    = [$this->testPlayer, $this->otherPlayer];
            $playfield  = new Playfield($numCols, $numRows, $gewinnt);
            
            for($row = $numRows - 1; $row >= 0; $row--){
                if(($row - 1) % ($gewinnt - 1) == 0){       // Reihen immer gleich befüllen, bis eins weniger als benötigt erreicht ist, dann andersrum befüllen
                    $players = array_reverse($players);
                }
                    
                for($col = 0; $col < $numCols; $col++){
                    $playfield->insertToken($col, $players[$col % 2]);
                }                
            }
            
            $this->assertTrue($playfield->detectDraw());
        }
    }
    
    /**
     * Test, ob korrekt erkannt wird, dass bisher kein Unentschieden vorliegt
     */    
    public function testNotDetectDraw(){
        $playfield = new Playfield(7, 6, 4);
        
        foreach([2, 5, 2, 4, 5, 6, 6, 6] as $col){
            $playfield->insertToken($col, $this->testPlayer);
        }
        
        $this->assertFalse($playfield->detectDraw());
    }    
}