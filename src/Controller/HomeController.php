<?php

namespace App\Controller;

use App\Api\WalletApi;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class HomeController extends AbstractController
{
    #[Route('/', name: 'app_home')]
    public function index(WalletApi $walletApi): Response
    {
        $walletApi->list();
        return $this->render('home/wallets.html.twig');
    }
}
