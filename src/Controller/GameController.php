<?php
namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

use App\Utils\Playfield;

class GameController extends AbstractController
{
    /**
     * @Route("/game", name="game")
     */
    public function index(SessionInterface $session){
        $numCols = 7;
        $numRows = 6;
        $gewinnt = 4;
        
        $session->set('playfield', new Playfield($numCols, $numRows, $gewinnt));
        $session->set('currentPlayer', 1);
        
        return $this->render('game/index.html.twig', [
            'maxCol'    => $numCols - 1,
            'maxRow'    => $numRows - 1,            
            'gewinnt'   => $gewinnt,
            'reportUrl' => $this->generateUrl('report')
        ]);
    }
    
    /**
     * @Route("/processMove", name="processMove")
     */
    public function processMove(Request $request, SessionInterface $session){
        $playfield  = $session->get('playfield');  // TODO wirklich so?
        $player     = $session->get('currentPlayer');
        $playerWins = false;
        
        $col        = $request->request->get('col');
        $row        = $playfield->insertToken($col, $player);        
        
        if($row >= 0){
            $playerWins = $playfield->detectWin($col, $row, $player);
        
            $nextPlayer = 3 - $player;
            $session->set('currentPlayer', $nextPlayer);
        }
        
        unset($playfield);
        
        return $this->json([
            'row'           => $row,
            'player'        => $player,
            'playerWins'    => $playerWins
        ]);
    }
}
