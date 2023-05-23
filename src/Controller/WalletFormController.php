<?php

namespace App\Controller;

use App\Api\GS\Wallet;
use App\Api\GS\WalletCreateForm;
use App\Api\WalletApi;
use App\Form\WalletCreateType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;

#[Route('/wallet_form')]
class WalletFormController extends AbstractController
{
    #[Route('/create', name: 'app_wallet_form_create')]
    public function create(Request $request, WalletApi $walletApi): Response
    {

        $wallet = new WalletCreateForm;

        $form = $this->createForm(WalletCreateType::class, $wallet)->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid())
        {
            try
            {
                $walletApi->create
                (
                    $wallet->name,
                    $wallet->passphrase,
                    $wallet->avoid_reuse
                );
                $this->addFlash('success', 'Wallet created');
                return $this->redirectToRoute('app_home');
            }
            catch(\Exception $e)
            {
                $this->addFlash('danger', $e->getMessage());
                return $this->redirectToRoute('app_wallet_form_create');
            }
            
        }

        return $this->render('wallet_form/create.html.twig', [
            'form' => $form->createView()
        ]);
    }
}
