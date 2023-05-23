<?php

namespace App\Controller;

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
}
