<?php

namespace App\Controller;

use App\Api\WalletApi;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class HomeController extends AbstractController
{
    #[Route('/', name: 'app_home')]
    public function index(WalletApi $walletApi): Response
    {

        $wallets = [];

        try
        {
            $wallets = $walletApi->list();
        }
        catch(\Exception $e)
        {
            $this->addFlash('danger', $e->getMessage());
        }

        return $this->render('home/wallets.html.twig', [
            'wallets' => $wallets
        ]);
    }
}
