<?php
namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

/**
 * Controller für die Auswertungsseite inkl. Login
 */
class ReportController extends AbstractController
{
    /**
     * Anzeigen der Login-Seite
     *
     * @return Response Das gerenderte Template
     *
     * @Route("/report", name="report")
     */
    public function index(){
        return $this->render('report/login.html.twig', [
            'checkLoginUrl' => $this->generateUrl('checkLogin')
        ]);
    }
    
    /**
     * Auswertung der Login-Daten. Bei Erfolg Anzeigen der Auswertungsseite, ansonsten zurück zum Login
     *
     * @param Request           $request    Enthält die Login-Daten
     * @param SessionInterface  $session    Die Spieldaten werden in der Session vorgehalten
     *
     * @return Response     Das gerenderte Template (Auswertungsseite oder Login)
     *
     * @Route("/checkLogin", name="checkLogin")
     */
    public function checkLogin(Request $request, SessionInterface $session){        
        $logins = [[
                'username' => 'real.digital',
                'password' => '$2y$10$aBJlWtLe2geQUNFsz3MLeuSMwniKBDElE34KhKIyP0A4byEknMpGu'
            ],[
                'username' => 'spiral_generator',
                'password' => '$2y$10$M1C4VIlTeABSXj/T.NF1C.dLgWPfDnoFYz5ejcsUqd4KFsopMs2m6'
        ]];
        
        $user = $request->request->get('username');
        $pass = $request->request->get('password');        
        
        $doLogin = false;
        foreach($logins as $login){
            if(
                   $login['username'] == $user
                && password_verify($pass, $login['password'])
            ){
                $doLogin = true;
                break;
            }
        }
        
        if($doLogin){
            return $this->render('report/report.html.twig', [
                'info'      => $session->get('playfield')->generateReport(),
                'reloadUrl' => $this->generateUrl('game')
            ]);
        }
        
        return $this->render('report/login.html.twig', [
            'checkLoginUrl' => $this->generateUrl('checkLogin'),
            'message'       => 'Username oder Passwort inkorrekt.',
            'username'      => $user,
            'password'      => $pass
        ]);        
    }
}
