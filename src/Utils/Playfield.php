<?php
namespace App\Utils;

class Playfield {    
    private $field,
            $maxCol,
            $maxRow,
            $needToFind,
            
            $winner = null,
            $turns  = 0;
            
    public function __construct($numCols, $numRows, $gewinnt){
        $this->maxCol       = $numCols - 1;
        $this->maxRow       = $numRows - 1;
        $this->needToFind   = $gewinnt - 1;
        
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
        if($this->field[$col][$row] !== $player){
            return false;
        }
    
        $endReached = array_fill(-1, 3, array_fill(-1, 3, false));
        $sameFound  = array_fill(-1, 3, array_fill(-1, 3, 0));
        $playerWins = false;
        
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
                                || $this->field[$col + $checkCol][$row + $checkRow] !== $player
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
        
        foreach([[0, 1], [1, 0], [1, 1], [1, -1]] as $directions){
            $x = $directions[0];
            $y = $directions[1];
            
            $numFound =   $sameFound[$x][$y]
                        + $sameFound[-$x][-$y];
            
            if($numFound >= $this->needToFind){
                $playerWins = true;
                $this->winner = $player;
                break;
            }            
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
            $highestByCol[$colIndex] = 0;
            
            foreach($colValues as $rowIndex => $cellValue){
                if($cellValue){
                    $highestByCol[$colIndex] = $this->maxRow - $rowIndex + 1;
                    break;
                }
            }
        }
        
        sort($highestByCol);
        $highest    = end($highestByCol);
        $lowest     = $highestByCol[0];
        
        return [
            'mode'      => $this->needToFind + 1,
            'winner'    => $this->winner,
            'turns'     => $this->turns,
            'highest'   => $highest,
            'lowest'    => $lowest
        ];
    }
}