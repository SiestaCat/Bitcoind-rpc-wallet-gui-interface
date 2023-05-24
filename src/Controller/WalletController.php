<?php

namespace App\Controller;

use App\Api\WalletApi;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/wallet')]
class WalletController extends AbstractController
{
    #[Route('/show/{wallet_name}', name: 'app_wallet_show')]
    public function show(string $wallet_name, WalletApi $walletApi): Response
    {
        return $this->render('wallet/show.html.twig', [
            'wallet' => $walletApi->get($wallet_name),
            'transactions' => $walletApi->listtransactions($wallet_name)
        ]);
    }

    #[Route('/load/{wallet_name}', name: 'app_wallet_load', methods: ['POST'])]
    public function load_wallet(string $wallet_name, Request $request, WalletApi $walletApi): Response
    {
        if ($this->isCsrfTokenValid('load_wallet'.$wallet_name, $request->request->get('_token'))) {
            try
            {
                $walletApi->load($wallet_name);
                $this->addFlash('success', 'Wallet loaded');
                return $this->redirectToRoute('app_home');
            }
            catch(\Exception $e)
            {
                $this->addFlash('danger', $e->getMessage());
                return $this->redirectToRoute('app_home');
            }
        } else $this->addFlash('danger', 'Invalid token');

        return $this->redirectToRoute('app_home', [], Response::HTTP_SEE_OTHER);
    }

    #[Route('/generate_address/{wallet_name}', name: 'app_wallet_generate_address', methods: ['POST'])]
    public function generate_address(string $wallet_name, Request $request, WalletApi $walletApi): Response
    {
        if ($this->isCsrfTokenValid('generate_address'.$wallet_name, $request->request->get('_token'))) {
            try
            {
                $address = $walletApi->getnewaddress($wallet_name, $request->request->get('address_type'));
                $this->addFlash('success', 'Address created ' . $address);
                return $this->redirectToRoute('app_wallet_show', ['wallet_name' => $wallet_name]);
            }
            catch(\Exception $e)
            {
                $this->addFlash('danger', $e->getMessage());
                return $this->redirectToRoute('app_wallet_show', ['wallet_name' => $wallet_name]);
            }
        } else $this->addFlash('danger', 'Invalid token');

        return $this->redirectToRoute('app_wallet_show', ['wallet_name' => $wallet_name], Response::HTTP_SEE_OTHER);
    }
}
