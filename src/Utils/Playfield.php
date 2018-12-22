<?php
namespace App\Utils;

/**
 * Enthält die Spiellogik
 */
class Playfield {    
    private $field,
            $maxCol,
            $maxRow,
            $needToFind,
            
            $winner = null,
            $turns  = 0;
    
    /**
     * @param int   $numCols    Anzahl Spalten für das Spielfeld
     * @param int   $numRows    Anzahl Reihen für das Spielfeld
     * @param int   $gewinnt    Länge der zum Sieg benötigten Kette
     */
    public function __construct($numCols, $numRows, $gewinnt){
        $this->maxCol       = $numCols - 1;
        $this->maxRow       = $numRows - 1;
        $this->needToFind   = $gewinnt - 1;     // von einem neu gesetzten Spielstein ausgehend müssen soviel weitere gefunden werden (z.B. 3 bei 4 gewinnt)
        
        // Spielfeld mit 0 initialisieren ( = kein Stein gesetzt)
        $this->field = array_fill(0, $numCols,
            array_fill(0, $numRows, 0)
        );
    }
    
    /**
     * Neuen Spielstein einsetzen (richtige Reihe anhand der übergebenen Spalte ermitteln) sowie Runden zählen
     *
     * @param int   $col            Die Spalte, in die ein neuer Spielstein "eingeworfen" wurde
     * @param int   $currentPlayer  Die Nummer des Spielers, dessen Spielstein "eingeworfen" wurde
     *
     * @return int  Die ermittelte Reihe für den eingeworfenen Spielstein (-1 = Es ist kein Platz mehr in dieser Spalte)
     */
    public function insertToken($col, $currentPlayer){
        // TODO diese Schleife könnte eingespart werden, wenn in einem Session-Array die aktuelle Höhe jeder Spalte hinterlegt wäre, bräuchte dann nur hochgezählt zu werden
        // würde auch die Auswertung betreffen!!
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
    
    /**
     * Ermitteln, ob der zuletzt eingeworfene Spielstein zum Sieg für den jeweiligen Spieler führt
     *
     * @param int   $col    Die Spalte des eingeworfenen Spielsteins
     * @param int   $row    Die Reihe des eingeworfenen Spielsteins
     * @param int   $player Die Nummer des Spielers, dessen Spielstein "eingeworfen" wurde
     *
     * @return bool Wurde eine ausreichend lange Kette von Steinen gefunden?
     */
    public function detectWin($col, $row, $player){
        if($this->field[$col][$row] !== $player){
            return false;
        }
    
        $endReached = array_fill(-1, 3, array_fill(-1, 3, false));
        $sameFound  = array_fill(-1, 3, array_fill(-1, 3, 0));
        $playerWins = false;
        
        // Ausgehend von der Position des neuen Steins in sieben Richtungen (s.u.) nach ausreichend langen Ketten suchen
        for($cellDistance = 1; $cellDistance <= $this->needToFind; $cellDistance++){
            for($yDir = -1; $yDir <= 1; $yDir++){
                $checkRow = $row + $yDir * $cellDistance;
                
                for($xDir = -1; $xDir <= 1; $xDir++){                                        
                    if(    !$endReached[$xDir][$yDir]
                        && ($xDir || $yDir == 1)         // 0,0 braucht nicht berücksichtigt zu werden, "nach oben" muss ebenfalls nicht gesucht werden 
                    ){                    
                        $checkCol = $col + $xDir * $cellDistance;
                        
                        if(    $checkCol < 0
                            || $checkCol > $this->maxCol
                            || $checkRow < 0
                            || $checkRow > $this->maxRow
                            || $this->field[$checkCol][$checkRow] !== $player
                        ){
                            $endReached[$xDir][$yDir] = true;       // Spielfeldrand bzw. leeres oder gegnerisches Feld in dieser Richtung gefunden
                        } else {
                            $sameFound[$xDir][$yDir]++;             // Eigener Spielstein in dieser Richtung gefunden
                        }                        
                    }
                }
            }
        }
        
        // Anzahl gefundener eigener Steine aus entgegengesetzten Richtungen addieren (z.B. links oben und rechts unten)
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
    
    /**
     * Ermitteln, ob ein Unentschieden vorliegt (d.h. alle Felder sind befüllt, ohne dass sich eine ausreichend lange Kette ergibt)
     *
     * @return bool Liegt ein Unentschieden vor?
     */
    public function detectDraw(){
        $filledCols = 0;        
        foreach($this->field as $col){
            $filledCols += $col[0] > 0;     // Es muss jeweils nur das oberste Feld der jeweiligen Spalte berücksichtigt werden
        }
        
        return $filledCols == $this->maxCol + 1;
    }
    
    /**
     * Erstellen der Auswertung, inkl. Anzahl Runden sowie Höhe der am höchsten und am niedrigsten befüllten Spalte
     *
     * @return array[int] Die Auswertungsdaten
     */
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
            'cols'      => $this->maxCol + 1,
            'rows'      => $this->maxRow + 1,
            'winner'    => $this->winner,
            'turns'     => $this->turns,
            'highest'   => $highest,
            'lowest'    => $lowest
        ];
    }
}