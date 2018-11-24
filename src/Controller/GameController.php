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
    
        return $this->render('game/index.html.twig', [
            'maxCol'    => $numCols - 1,
            'maxRow'    => $numRows - 1,            
            'gewinnt'   => $gewinnt
        ]);
    }
    
    /**
     * @Route("/processMove", name="processMove")
     */
    public function processMove(Request $request, SessionInterface $session){
        $col = $request->request->get('col');
        $row = $session->get('playfield')
                       ->insertToken($col, 1); // TODO Spieler-Kennung statt 1
                       
        return $this->json(['row' => $row]);
    }
}
