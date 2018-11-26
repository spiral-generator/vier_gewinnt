<?php
namespace App\Tests\Utils;

use App\Utils\Playfield;
use PHPUnit\Framework\TestCase;

class PlayfieldTest extends TestCase {
    private $testPlayer     = 1,
            $otherPlayer    = 2,
            
            $randomSeed     = 3;
  
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
    
    private function detectWinUpDown($numCols, $numRows, $gewinnt){
        $playfield  = new Playfield($numCols, $numRows, $gewinnt);
        
        for($token = 0; $token < $gewinnt; $token++){
            $lastRow = $playfield->insertToken(0, $this->testPlayer);
        }
        
        $this->assertTrue(
            $playfield->detectWin(0, $lastRow, $this->testPlayer)
        );
        $this->assertFalse(
            $playfield->detectWin(0, $lastRow, $this->otherPlayer)
        );
    }
    
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
        $this->assertFalse(
            $playfield->detectWin($lastColumn, $lastRow, $this->otherPlayer)
        );    
    }
    
    private function detectWinLeftRight($numCols, $numRows, $gewinnt){
        $playfield = new Playfield($numCols, $numRows, $gewinnt);
        for($column = 0; $column < $gewinnt; $column++){
            $lastRow = $playfield->insertToken($column, $this->testPlayer);
            $lastColumn = $column;
        }
        
        $this->assertTrue(
            $playfield->detectWin($lastColumn, $lastRow, $this->testPlayer)
        );       
        $this->assertFalse(
            $playfield->detectWin($lastColumn, $lastRow, $this->otherPlayer)
        );    
    }
    
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
    
    public function testDetectDraw(){
        mt_srand($this->randomSeed);
        
        for($run = 0; $run <= 9; $run++){
            $numRows    = mt_rand(2, 12);
            $numCols    = mt_rand($numRows, 12);
            $gewinnt    = mt_rand(2, $numCols - 1);
            $players    = [$this->testPlayer, $this->otherPlayer];
            $playfield  = new Playfield($numCols, $numRows, $gewinnt);
            
            for($row = $numRows - 1; $row >= 0; $row--){
                if(($row - 1) % ($gewinnt - 1) == 0){
                    $players = array_reverse($players);
                }
                    
                for($col = 0; $col < $numCols; $col++){
                    $playfield->insertToken($col, $players[$col % 2]);
                }                
            }
            
            $this->assertTrue($playfield->detectDraw());
        }
    }
}