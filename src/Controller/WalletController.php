<?php

namespace App\Controller;

use App\Api\GS\Wallet;
use App\Api\WalletApi;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/wallet')]
class WalletController extends AbstractController
{

    const ADDRESSES_TYPES = [
        'legacy' => true, //default checked
        'p2sh-segwit' => false,
        'bech32' => false
    ];

    #[Route('/show/{wallet_name}', name: 'app_wallet_show')]
    public function show(string $wallet_name, WalletApi $walletApi): Response
    {

        $wallet = new Wallet;
        $transactions = [];

        try
        {
            $wallet = $walletApi->get($wallet_name);
            $transactions = $walletApi->listtransactions($wallet_name);
        }
        catch(\Exception $e)
        {
            $this->addFlash('danger', $e->getMessage());
        }

        

        return $this->render('wallet/show.html.twig', [
            'wallet_name' => $wallet_name,
            'wallet' => $wallet,
            'transactions' => $transactions,
            'addresses_types' => self::ADDRESSES_TYPES
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

    #[Route('/passphrase_change/{wallet_name}', name: 'app_wallet_passphrase_change', methods: ['POST'])]
    public function passphrase_change(string $wallet_name, Request $request, WalletApi $walletApi): Response
    {
        if ($this->isCsrfTokenValid('passphrase_change'.$wallet_name, $request->request->get('_token'))) {
            try
            {
                $walletApi->walletpassphrasechange($wallet_name, $request->request->get('old_passphrase'), $request->request->get('new_passphrase'));
                $this->addFlash('success', 'Passphrase changed');
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

    #[Route('/send/{wallet_name}', name: 'app_wallet_send', methods: ['POST'])]
    public function send(string $wallet_name, Request $request, WalletApi $walletApi): Response
    {
        if ($this->isCsrfTokenValid('wallet_send'.$wallet_name, $request->request->get('_token'))) {
            try
            {
                $walletApi->walletpassphrasechange($wallet_name, $request->request->get('old_passphrase'), $request->request->get('new_passphrase'));
                $this->addFlash('success', 'Sended');
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
