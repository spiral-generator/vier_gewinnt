<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;

class GameController extends AbstractController
{
    /**
     * @Route("/game", name="game")
     */
    public function index()
    {
        $gewinnt = 4;
        $numRows = 6;
        $numCols = 7;
    
        return $this->render('game/index.html.twig', [
            'gewinnt'   => $gewinnt,
            'maxRow'    => $numRows - 1,
            'maxCol'    => $numCols - 1
        ]);
    }
}
