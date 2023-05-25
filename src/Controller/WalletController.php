<?php

namespace App\Controller;

use App\Api\GS\Wallet;
use App\Api\WalletApi;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
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
        $send_fees = [];

        try
        {
            $wallet = $walletApi->get($wallet_name);
            $transactions = $walletApi->listtransactions($wallet_name);
        }
        catch(\Exception $e)
        {
            $this->addFlash('danger', $e->getMessage());
        }

        try
        {
            $send_fees = $walletApi->getSendFees($wallet_name);
        }
        catch(\Exception $e)
        {
            $this->addFlash('danger', 'Unable to get estimates fees');
            $this->addFlash('danger', $e->getMessage());
        }
        
        return $this->render('wallet/show.html.twig', [
            'wallet_name' => $wallet_name,
            'wallet' => $wallet,
            'transactions' => $transactions,
            'addresses_types' => self::ADDRESSES_TYPES,
            'send_fees' => $send_fees
        ]);
    }

    #[Route('/check_send_fee/{wallet_name}', name: 'app_wallet_check_send_fee', methods: ['POST'])]
    public function check_send_fee(string $wallet_name, Request $request, WalletApi $walletApi): JsonResponse
    {
        if ($this->isCsrfTokenValid('check_send_fee'.$wallet_name, $request->request->get('_token'))) {
            try
            {
                $send_to_address = $request->request->get('send_to_address');
                $send_amount = $request->request->get('send_amount');
                $send_fee = $request->request->get('send_fee');
                $subtract_fee_from_amount = $request->request->get('subtract_fee_from_amount') === '1';
                $response = $walletApi->getFee($wallet_name, $send_fee, $send_to_address, $send_amount);

                $balance_available = $walletApi->getbalances($wallet_name)->available;

                $json_response = (object)
                [
                    'fee' => $response->fee,
                    'send_amount_plus_fee' => rtrim(bcadd($send_amount, $response->fee, 99), '0'),
                    'balance_available' => $balance_available
                ];

                $json_response->balance_fee_diff = rtrim(bcsub($balance_available, $json_response->send_amount_plus_fee, 99), '0');

                if(!$subtract_fee_from_amount)
                {
                    
                    if(bccomp($json_response->send_amount_plus_fee, $balance_available, 99) === 1) $json_response->error = 'Insufficient funds ' . $json_response->balance_fee_diff;
                }

                return new JsonResponse($json_response);
                
            }
            catch(\Exception $e)
            {
                if(str_contains($e->getMessage(), 'Insufficient funds') || str_contains($e->getMessage(), 'The transaction amount is too small to pay the fee'))
                {
                    return new JsonResponse(['error' => 'Insufficient funds']);
                }
                else throw $e;
            }
        } else return new JsonResponse(['error' => 'Invalid token']);
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
                $walletApi->send
                (
                    $wallet_name,
                    $request->request->get('send_to_address'),
                    $request->request->get('send_amount'),
                    $request->request->get('send_fee')
                );
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
