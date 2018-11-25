<?php
namespace App\Utils;

class Playfield {    
    private $field,
            $maxCol,
            $maxRow,
            $needToFind,
            
            $winner = null,
            $turns = 0;
            
    public function __construct($numCols, $numRows, $gewinnt){
        $this->maxCol       = $numCols - 1;
        $this->maxRow       = $numRows - 1;
        $this->needToFind   = $gewinnt - 1;     // TODO varname?
        
        $this->field = array_fill(0, $numCols,
            array_fill(0, $numRows, 0)
        );
    }
    
    public function insertToken($col, $currentPlayer){
        for($row = $this->maxRow; $row >= 0; $row--){
            if($this->field[$col][$row] == 0){
                break;
            }
        }
        
        if($row >= 0){
            $this->field[$col][$row] = $currentPlayer;
            
            if($currentPlayer == 1){
                $this->turns++;
            }
        }
        
        return $row;
    }
    
    public function detectWin($col, $row, $player){
        $endReached = array_fill(-1, 3, array_fill(-1, 3, false));
        $sameFound  = array_fill(-1, 3, array_fill(-1, 3, 0));
        
        for($cellDistance = 1; $cellDistance <= $this->needToFind; $cellDistance++){
            foreach([-$cellDistance, 0, $cellDistance] as $checkRow){
                foreach([-$cellDistance, 0, $cellDistance] as $checkCol){
                    if($checkCol || $checkRow){
                        $xDir = $checkCol <=> 0;
                        $yDir = $checkRow <=> 0;
                        
                        if(!$endReached[$xDir][$yDir]){
                            if(
                                   $col + $checkCol < 0
                                || $col + $checkCol > $this->maxCol
                                || $row + $checkRow < 0
                                || $row + $checkRow > $this->maxRow
                                || $this->field[$col + $checkCol][$row + $checkRow] !== $this->field[$col][$row]
                            ){
                                $endReached[$xDir][$yDir] = true;
                            } else {
                                $sameFound[$xDir][$yDir]++;
                            }                        
                        }
                    }
                }
            }
        }
        
        $playerWins = false;
        foreach([[0, 1], [1, 0], [1, 1], [1, -1]] as $directions){
            $numFound = 0;
            
            foreach([1, -1] as $flip){
                $x = $directions[0] * $flip;
                $y = $directions[1] * $flip;
                
                $numFound += $sameFound[$x][$y];
            }
            
            if($numFound >= $this->needToFind){
                $playerWins = true;
                break;
            }            
        }
        
        if($playerWins){
            $this->winner = $player;
        }
        
        return $playerWins;
    }
    
    public function detectDraw(){
        $filledCols = 0;        
        foreach($this->field as $col){
            $filledCols += $col[0] > 0;
        }
        
        return $filledCols == $this->maxCol + 1;
    }
    
    public function generateReport(){        
        $highestByCol = [];
        
        foreach($this->field as $colIndex => $colValues){
            foreach($colValues as $rowIndex => $cellValue){
                if($cellValue){
                    $highestByCol[$colIndex] = $this->maxRow - $rowIndex + 1;
                    break;
                }
            }
            if(!isset($highestByCol[$colIndex])){
                $highestByCol[$colIndex] = 0;
            }
        }
                      
        sort($highestByCol);
        $highest = end($highestByCol);
        $lowest = $highestByCol[0];
        
        return [
            'mode'      => $this->needToFind + 1,
            'winner'    => $this->winner,
            'turns'     => $this->turns,
            'highest'   => $highest,
            'lowest'    => $lowest
        ];
    }
}