<?php

namespace SAM\CommonBundle\Controller;

use SAM\CommonBundle\Form\Type\MyAccountType;
use SAM\AddressBookBundle\Form\Type\SearchContactMergedMobileType;
use SAM\AddressBookBundle\Form\Type\SearchContactMergedType;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\Routing\Annotation\Route;
use SAM\CommonBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\HttpFoundation\Session\Session;

class AccountController extends Controller
{
    /**
     * @param TokenStorageInterface $tokenStorage
     * @param Request               $request
     * @param ObjectManager         $om
     *
     * @return Response|RedirectResponse
     *
     * @Route("/mon-compte", name="edit_user_account")
     */
    public function editAction(TokenStorageInterface $tokenStorage, Request $request, ObjectManager $om)
    {
        $searchForm = $this->createForm(SearchContactMergedType::class);
        $searchFormMobile = $this->createForm(SearchContactMergedMobileType::class);

        $user = $tokenStorage->getToken()->getUser();
        $form = $this->createForm(MyAccountType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $om->flush();

            $this->addFlash(
                'success',
                'Votre compte a été mis à jour'
            );

            return $this->redirectToRoute('edit_user_account');
        }

        return $this->render('@SAMCommon/User/my_account.html.twig', [
            'form' => $form->createView(),
            'searchForm' => $searchForm->createView(),
            'searchFormMobile' => $searchFormMobile->createView(),
        ]);
    }

    /**
     * [menuToggledAction description]
     * @param  Request $request [description]
     *
     * @Route("/menu-toggled", name="user_menu_toggled", options={"expose"=true})
     */
    public function menuToggledAction(Request $request)
    {
        $session = new Session();
        $session->set('user.menu.toggled', $request->request->get('toggled'));

        return new JsonResponse([
                    'toggled' => $session->get('user.menu.toggled'),
                ]);
    }
}
