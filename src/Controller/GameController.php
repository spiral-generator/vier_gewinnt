<?php
namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

use App\Utils\Playfield;

/**
 * Controller für das eigentliche Spiel
 */
class GameController extends AbstractController
{
    /**
     * Startpunkt für die Anwendung, inkl. Mini-"Config" (Anzahl Spalten, Reihen, welche Ketten-Länge gewinnt)
     *
     * @param SessionInterface $session Die Spieldaten werden in der Session vorgehalten
     *
     * @return Response Das gerenderte Template
     *
     * @Route("/game", name="game")
     */
    public function index(SessionInterface $session){
        // "config"
        $numCols = 7;       // Anzahl Spalten
        $numRows = 6;       // Anzahl Reihen
        $gewinnt = 4;       // X gewinnt
        
        // $gewinnt darf maximal der Breite oder Höhe des Spielfeldes entsprechen, sonst kann das Spiel nicht gewonnen werden
        if($gewinnt > $numCols && $gewinnt > $numRows){
            $gewinnt = $numCols >= $numRows ?
                $numCols : $numRows;
        }
        
        $session->set('playfield', new Playfield($numCols, $numRows, $gewinnt));
        $session->set('currentPlayer', 1);
        
        return $this->render('game/index.html.twig', [
            'maxCol'        => $numCols - 1,
            'maxRow'        => $numRows - 1,            
            'gewinnt'       => $gewinnt,
            'reportUrl'     => $this->generateUrl('report'),
            'processUrl'    => $this->generateUrl('processMove')
        ]);
    }
    
    /**
     * Entgegennehmen und Verarbeiten der User-Aktionen
     *
     * @param Request           $request    Enthält die vom Client übergebenen Spieldaten
     * @param SessionInterface  $session    Die Spieldaten werden in der Session vorgehalten
     *
     * @return JsonResponse     Die Ergebnisse der serverseitigen Verarbeitung
     *
     * @Route("/processMove", name="processMove")
     */
    public function processMove(Request $request, SessionInterface $session){
        $playfield  = $session->get('playfield');
        $player     = $session->get('currentPlayer');
        $playerWins = false;
        $isDraw     = false;
        
        $col        = $request->request->get('col');
        $row        = $playfield->insertToken($col, $player);        
        
        if($row >= 0){
            $playerWins = $playfield->detectWin($col, $row, $player);
            
            // Unentschieden entsteht nur, wenn alle Felder voll sind (row 0 = oberste Zeile)
            if($row == 0 && !$playerWins){
                $isDraw = $playfield->detectDraw();
            }
            
            $nextPlayer = 3 - $player;
            $session->set('currentPlayer', $nextPlayer);
        }
        
        unset($playfield);
        
        return $this->json([
            'row'           => $row,
            'player'        => $player,
            'playerWins'    => $playerWins,
            'isDraw'        => $isDraw
        ]);
    }
}
