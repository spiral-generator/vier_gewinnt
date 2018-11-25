<?php
namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

class ReportController extends AbstractController
{
    /**
     * @Route("/report", name="report")
     */
    public function index(){
        return $this->render('report/login.html.twig', [
            'checkLoginUrl' => $this->generateUrl('checkLogin')
        ]);
    }
    
    /**
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
            $info = $session->get('playfield')->generateReport();
            $info['reloadUrl'] = $this->generateUrl('game');
            
            return $this->render('report/report.html.twig', $info);
        }
        
        return $this->render('report/login.html.twig', [
            'checkLoginUrl' => $this->generateUrl('checkLogin'),
            'message'       => 'Username oder Passwort inkorrekt.',
            'username'      => $user,
            'password'      => $pass
        ]);        
    }
}
