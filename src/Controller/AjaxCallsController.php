<?php

namespace App\Controller;

use App\Api\WalletApi;
use App\RpcBitcoin\Client;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/ajax_calls')]
class AjaxCallsController extends AbstractController
{
    #[Route('/is_ip', name: 'app_ajaxcall_is_up')]
    public function index(Client $client): JsonResponse
    {
        return new JsonResponse(['is_up' => $client->isUp()]);
    }

    #[Route('/load_wallet/{wallet_name}', name: 'app_ajaxcall_load_wallet')]
    public function load_wallet(string $wallet_name, WalletApi $walletApi): JsonResponse
    {
        $walletApi->load($wallet_name);
        return new Response;
    }
}
