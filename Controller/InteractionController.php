<?php

namespace SAM\CommonBundle\Controller;

// common
use SAM\CommonBundle\Form\Type\InteractionAppointmentType;
use SAM\CommonBundle\Form\Type\InteractionCallType;
use SAM\CommonBundle\Form\Type\InteractionEmailType;
use SAM\CommonBundle\Form\Type\InteractionLetterType;
use SAM\CommonBundle\Event\InteractionEvents;
use SAM\CommonBundle\Event\InteractionEvent;
use Symfony\Component\Routing\Annotation\Route;
use Doctrine\Common\Persistence\ObjectManager;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

// entities
use SAM\CommonBundle\Entity\Interaction;
use SAM\ProspectBundle\Entity\Prospect;

// forms
use SAM\CommonBundle\Form\Type\InteractionNoteType;

// voters
use SAM\CommonBundle\Security\InteractionVoter;

/**
* Class InteractionController
*
* @Route("/interaction")
*/
class InteractionController extends Controller
{
    /**
     * @param Request $request
     * @param ObjectManager $om
     * @param string $entity
     * @param int $id
     * @return Response|JsonResponse
     *
     * @Route("/{entity}/{id}/email/", name="interaction_email_create")
     */
    public function emailCreateAction(Request $request, ObjectManager $om, $entity, $id)
    {
        if (!$e = $this->findEntity($entity, $id, false)) {
            throw $this->createNotFoundException();
        }

        $isProspect = $e instanceOf Prospect;
        $email = $this->instantiateClass('interaction_email');

        $form = $this->createForm(InteractionEmailType::class, $email, [
            'action' => $this->generateUrl('interaction_email_create', ['entity' => $entity, 'id' => $id])
        ]);
        $form->handleRequest($request);

        if ($form->isValid() && $form->isSubmitted()) {
            $e->addInteractionEmail($email);

            $event = new InteractionEvent($email, Interaction::INTERACTION_TYPE_EMAIL, $e);
            $this->eventDispatcher->dispatch(InteractionEvents::INTERACTION_CREATE_SUCCESS, $event);

            $om->flush();

            $this->addFlash('success', 'L\'email a bien été enregistré.');

            return new JsonResponse(['redirectUrl' => $request->headers->get('referer')]);
        }

        return $this->render('@SAMCommon/Interaction/_email_form.html.twig', ['form' => $form->createView(), 'isProspect' => $isProspect]);
    }

    /**
     * @param Request $request
     * @param ObjectManager $om
     * @param string $entity
     * @param int $id
     *
     * @return Response|JsonResponse
     *
     * @Route("/{entity}/{entityId}/email/{id}/editer", name="interaction_email_update")
     */
    public function emailUpdateAction(Request $request, ObjectManager $om, $entity, $entityId, $id)
    {
        if (!$e = $this->findEntity($entity, $entityId)) {
            throw $this->createNotFoundException();
        }

        if (!$email = $this->findEntity('interaction_email', $id)) {
            throw $this->createNotFoundException();
        }

        if (!$this->isGranted(InteractionVoter::EDIT, $email)) {
            throw $this->createAccessDeniedException();
        }

        $form = $this->createForm(InteractionEmailType::class, $email, [
            'action' => $this->generateUrl('interaction_email_update', ['entity' => $entity, 'entityId' => $entityId, 'id' => $id])
        ]);
        $form->handleRequest($request);
        if ($form->isValid() && $form->isSubmitted()) {
            $om->persist($email);
            $this->addFlash('success', 'L\'email a bien été enregistré.');

            $event = new InteractionEvent($email, Interaction::INTERACTION_TYPE_EMAIL, $e);
            $this->eventDispatcher->dispatch(InteractionEvents::INTERACTION_EDIT_SUCCESS, $event);
            
            $om->flush();

            return new JsonResponse(['redirectUrl' => $request->headers->get('referer')]);
        }

        return $this->render('@SAMCommon/Interaction/_email_form.html.twig', [
            'form' => $form->createView(),
            'isProspect' => $entity === 'prospect'
        ]);
    }

    /**
     * @param Request $request
     * @param ObjectManager $om
     * @param string $entity
     * @param int $entityId
     * @param int $id
     *
     * @return Response|JsonResponse
     *
     * @Route("/{entity}/{entityId}/email/{id}/supprimer", name="interaction_email_remove")
     */
    public function emailRemoveAction(Request $request, ObjectManager $om, $entity, $entityId, $id)
    {
        if (!$e = $this->findEntity($entity, $entityId)) {
            throw $this->createNotFoundException();
        }

        if (!$email = $this->findEntity('interaction_email', $id)) {
            throw $this->createNotFoundException();
        }

        if (!$this->isGranted(InteractionVoter::REMOVE, $email)) {
            throw $this->createAccessDeniedException();
        }

        $event = new InteractionEvent($email, Interaction::INTERACTION_TYPE_EMAIL, $e);
        $this->eventDispatcher->dispatch(InteractionEvents::INTERACTION_REMOVE_INITIALIZE, $event);

        $e->removeInteractionEmail($email);

        $om->flush();

        $this->addFlash('success', 'L\'email a bien été supprimé');

        if ($request->headers->get('referer')) {
            if ($request->headers->get('referer')) {
            return $this->redirect($request->headers->get('referer'));
        } else {
            return $this->generateRouteToEntity($entity, $entityId);
        }
        } else {
            return $this->generateRouteToEntity($entity, $entityId);
        }
    }

    /**
     * @param Request $request
     * @param ObjectManager $om
     * @param string $entity
     * @param int $id
     * @return Response|JsonResponse
     *
     * @Route("/{entity}/{id}/rdv/", name="interaction_appointment_create")
     */
    public function appointmentCreateAction(Request $request, ObjectManager $om, $entity, $id)
    {
        if (!$e = $this->findEntity($entity, $id, false)) {
            throw $this->createNotFoundException();
        }

        $isProspect = $e instanceOf Prospect;
        $appointment = $this->instantiateClass('interaction_appointment');

        $form = $this->createForm(InteractionAppointmentType::class, $appointment, [
            'action' => $this->generateUrl('interaction_appointment_create', ['entity' => $entity, 'id' => $id])
        ]);
        $form->handleRequest($request);

        if ($form->isValid() && $form->isSubmitted()) {
            $e->addInteractionAppointment($appointment);
        
            $event = new InteractionEvent($appointment, Interaction::INTERACTION_TYPE_APPOINTMENT, $e);
            $this->eventDispatcher->dispatch(InteractionEvents::INTERACTION_CREATE_SUCCESS, $event);

            $om->flush();
            $this->addFlash('success', 'Le rendez-vous a bien été enregistré.');

            return new JsonResponse(['redirectUrl' => $request->headers->get('referer')]);
        }

        return $this->render('@SAMCommon/Interaction/_appointment_form.html.twig', ['form' => $form->createView(), 'isProspect' => $isProspect]);
    }

    /**
     * @param Request $request
     * @param ObjectManager $om
     * @param string $entity
     * @param int $id
     *
     * @return Response|JsonResponse
     *
     * @Route("/{entity}/{entityId}/rdv/{id}/editer", name="interaction_appointment_update")
     */
    public function appointmentUpdateAction(Request $request, ObjectManager $om, $entity, $entityId, $id)
    {
        if (!$e = $this->findEntity($entity, $entityId)) {
            throw $this->createNotFoundException();
        }

        if (!$appointment = $this->findEntity('interaction_appointment', $id)) {
            throw $this->createNotFoundException();
        }

        if (!$this->isGranted(InteractionVoter::EDIT, $appointment)) {
            throw $this->createAccessDeniedException();
        }

        $form = $this->createForm(InteractionAppointmentType::class, $appointment, [
            'action' => $this->generateUrl('interaction_appointment_update', ['entity' => $entity, 'entityId' => $entityId, 'id' => $id])
        ]);
        $form->handleRequest($request);

        if ($form->isValid() && $form->isSubmitted()) {
            $om->persist($appointment);
            
            $event = new InteractionEvent($appointment, Interaction::INTERACTION_TYPE_APPOINTMENT, $e);
            $this->eventDispatcher->dispatch(InteractionEvents::INTERACTION_EDIT_SUCCESS, $event);

            $om->flush();
            $this->addFlash('success', 'Le rendez-vous a bien été mis à jour.');

            return new JsonResponse(['redirectUrl' => $request->headers->get('referer')]);
        }

        return $this->render('@SAMCommon/Interaction/_appointment_form.html.twig', [
            'form' => $form->createView(),
            'isProspect' => $entity === 'prospect'
        ]);
    }

    /**
     * @param Request $request
     * @param ObjectManager $om
     * @param string $entity
     * @param int $entityId
     * @param int $id
     *
     * @return Response|JsonResponse
     *
     * @Route("/{entity}/{entityId}/rdv/{id}/supprimer", name="interaction_appointment_remove")
     */
    public function appointmentRemoveAction(Request $request, ObjectManager $om, $entity, $entityId, $id)
    {
        if (!$e = $this->findEntity($entity, $entityId)) {
            throw $this->createNotFoundException();
        }

        if (!$appointment = $this->findEntity('interaction_appointment', $id)) {
            throw $this->createNotFoundException();
        }

        if (!$this->isGranted(InteractionVoter::REMOVE, $appointment)) {
            throw $this->createAccessDeniedException();
        }

        $event = new InteractionEvent($appointment, Interaction::INTERACTION_TYPE_APPOINTMENT, $e);
        $this->eventDispatcher->dispatch(InteractionEvents::INTERACTION_REMOVE_INITIALIZE, $event);

        $e->removeInteractionAppointment($appointment);

        $om->flush();
        $this->addFlash('success', 'Le rendez-vous a bien été supprimé');

        if ($request->headers->get('referer')) {
            return $this->redirect($request->headers->get('referer'));
        } else {
            return $this->generateRouteToEntity($entity, $entityId);
        }
    }

    /**
     * @param Request $request
     * @param ObjectManager $om
     * @param string $entity
     * @param int $id
     * @return Response|JsonResponse
     *
     * @Route("/{entity}/{id}/appel/", name="interaction_call_create")
     */
    public function callCreateAction(Request $request, ObjectManager $om, $entity, $id)
    {
        if (!$e = $this->findEntity($entity, $id, false)) {
            throw $this->createNotFoundException();
        }

        $isProspect = $e instanceOf Prospect;
        $call = $this->instantiateClass('interaction_call');

        $form = $this->createForm(InteractionCallType::class, $call, [
            'action' => $this->generateUrl('interaction_call_create', ['entity' => $entity, 'id' => $id])
        ]);
        $form->handleRequest($request);

        if ($form->isValid() && $form->isSubmitted()) {
            $e->addInteractionCall($call);

            $event = new InteractionEvent($call, Interaction::INTERACTION_TYPE_CALL, $e);
            $this->eventDispatcher->dispatch(InteractionEvents::INTERACTION_CREATE_SUCCESS, $event);

            $om->flush();
            $this->addFlash('success', 'L\'appel a bien été enregistré');

            return new JsonResponse(['redirectUrl' => $request->headers->get('referer')]);
        }

        return $this->render('@SAMCommon/Interaction/_call_form.html.twig', ['form' => $form->createView(), 'isProspect' => $isProspect]);
    }

    /**
     * @param Request $request
     * @param ObjectManager $om
     * @param string $entity
     * @param int $entityId
     * @param int $id
     *
     * @return Response|JsonResponse
     *
     * @Route("/{entity}/{entityId}/appel/{id}/editer", name="interaction_call_update")
     */
    public function callUpdateAction(Request $request, ObjectManager $om, $entity, $entityId, $id)
    {
        if (!$e = $this->findEntity($entity, $entityId)) {
            throw $this->createNotFoundException();
        }

        if (!$call = $this->findEntity('interaction_call', $id)) {
            throw $this->createNotFoundException();
        }

        if (!$this->isGranted(InteractionVoter::EDIT, $call)) {
            throw $this->createAccessDeniedException();
        }

        $form = $this->createForm(InteractionCallType::class, $call, [
            'action' => $this->generateUrl('interaction_call_update', ['entity' => $entity, 'entityId' => $entityId, 'id' => $id])
        ]);
        $form->handleRequest($request);

        if ($form->isValid() && $form->isSubmitted()) {
            $om->persist($call);

            $event = new InteractionEvent($call, Interaction::INTERACTION_TYPE_CALL, $e);
            $this->eventDispatcher->dispatch(InteractionEvents::INTERACTION_EDIT_SUCCESS, $event);

            $om->flush();
            $this->addFlash('success', 'L\'appel a bien été enregistré');

            return new JsonResponse(['redirectUrl' => $request->headers->get('referer')]);
        }

        return $this->render('@SAMCommon/Interaction/_call_form.html.twig', [
            'form' => $form->createView(),
            'isProspect' => $entity === 'prospect'
        ]);
    }

    /**
     * @param Request $request
     * @param ObjectManager $om
     * @param string $entity
     * @param int $entityId
     * @param int $id
     *
     * @return Response|JsonResponse
     *
     * @Route("/{entity}/{entityId}/appel/{id}/supprimer", name="interaction_call_remove")
     */
    public function callRemoveAction(Request $request, ObjectManager $om, $entity, $entityId, $id)
    {
        if (!$e = $this->findEntity($entity, $entityId)) {
            throw $this->createNotFoundException();
        }

        if (!$call = $this->findEntity('interaction_call', $id)) {
            throw $this->createNotFoundException();
        }

        if (!$this->isGranted(InteractionVoter::REMOVE, $call)) {
            throw $this->createAccessDeniedException();
        }
        
        $event = new InteractionEvent($call, Interaction::INTERACTION_TYPE_CALL, $e);
        $this->eventDispatcher->dispatch(InteractionEvents::INTERACTION_REMOVE_INITIALIZE, $event);

        $e->removeInteractionCall($call);
        
        $om->flush();
        $this->addFlash('success', 'L\'appel a bien été supprimé');

        if ($request->headers->get('referer')) {
            return $this->redirect($request->headers->get('referer'));
        } else {
            return $this->generateRouteToEntity($entity, $entityId);
        }
    }

    /**
     * @param Request $request
     * @param ObjectManager $om
     * @param string $entity
     * @param int $id
     * @return Response|JsonResponse
     *
     * @Route("/{entity}/{id}/lettre/", name="interaction_letter_create")
     */
    public function letterCreateAction(Request $request, ObjectManager $om, $entity, $id)
    {
        if (!$e = $this->findEntity($entity, $id, false)) {
            throw $this->createNotFoundException();
        }

        $isProspect = $e instanceOf Prospect;
        $letter = $this->instantiateClass('interaction_letter');

        $form = $this->createForm(InteractionLetterType::class, $letter, [
            'action' => $this->generateUrl('interaction_letter_create', ['entity' => $entity, 'id' => $id])
        ]);
        $form->handleRequest($request);

        if ($form->isValid() && $form->isSubmitted()) {
            $e->addInteractionLetter($letter);

            $event = new InteractionEvent($letter, Interaction::INTERACTION_TYPE_LETTER, $e);
            $this->eventDispatcher->dispatch(InteractionEvents::INTERACTION_CREATE_SUCCESS, $event);

            $om->flush();
            $this->addFlash('success', 'Le courrier a bien été enregistré.');

            return new JsonResponse(['redirectUrl' => $request->headers->get('referer')]);
        }

        return $this->render('@SAMCommon/Interaction/_letter_form.html.twig', ['form' => $form->createView(), 'isProspect' => $isProspect]);
    }

    /**
     * @param Request $request
     * @param ObjectManager $om
     * @param int $id
     *
     * @return Response|JsonResponse
     *
     * @Route("/{entity}/{entityId}/lettre/{id}/editer", name="interaction_letter_update")
     */
    public function letterUpdateAction(Request $request, ObjectManager $om, $entity, $entityId, $id)
    {
        if (!$e = $this->findEntity($entity, $entityId)) {
            throw $this->createNotFoundException();
        }

        if (!$letter = $this->findEntity('interaction_letter', $id)) {
            throw $this->createNotFoundException();
        }

        if (!$this->isGranted(InteractionVoter::EDIT, $letter)) {
            throw $this->createAccessDeniedException();
        }

        $form = $this->createForm(InteractionLetterType::class, $letter, [
            'action' => $this->generateUrl('interaction_letter_update', ['entity' => $entity, 'entityId' => $entityId, 'id' => $id])
        ]);
        $form->handleRequest($request);

        if ($form->isValid() && $form->isSubmitted()) {
            $om->persist($letter);
            
            $event = new InteractionEvent($letter, Interaction::INTERACTION_TYPE_LETTER, $e);
            $this->eventDispatcher->dispatch(InteractionEvents::INTERACTION_EDIT_SUCCESS, $event);

            $om->flush();
            $this->addFlash('success', 'Le courrier a bien été enregistré.');

            return new JsonResponse(['redirectUrl' => $request->headers->get('referer')]);
        }

        return $this->render('@SAMCommon/Interaction/_letter_form.html.twig', [
            'form' => $form->createView(),
            'isProspect' => $entity === 'prospect'
        ]);
    }

    /**
     * @param Request $request
     * @param ObjectManager $om
     * @param string $entity
     * @param int $entityId
     * @param int $id
     *
     * @return Response|JsonResponse
     *
     * @Route("/{entity}/{entityId}/lettre/{id}/supprimer", name="interaction_letter_remove")
     */
    public function letterRemoveAction(Request $request, ObjectManager $om, $entity, $entityId, $id)
    {
        if (!$e = $this->findEntity($entity, $entityId)) {
            throw $this->createNotFoundException();
        }

        if (!$letter = $this->findEntity('interaction_letter', $id)) {
            throw $this->createNotFoundException();
        }

        if (!$this->isGranted(InteractionVoter::REMOVE, $letter)) {
            throw $this->createAccessDeniedException();
        }
        
        $event = new InteractionEvent($letter, Interaction::INTERACTION_TYPE_LETTER, $e);
        $this->eventDispatcher->dispatch(InteractionEvents::INTERACTION_REMOVE_INITIALIZE, $event);

        $e->removeInteractionLetter($letter);

        $om->flush();
        $this->addFlash('success', 'Le courrier a bien été supprimé');

        if ($request->headers->get('referer')) {
            return $this->redirect($request->headers->get('referer'));
        } else {
            return $this->generateRouteToEntity($entity, $entityId);
        }
    }

    /**
     * @param Request $request
     * @param ObjectManager $om
     * @param string $entity
     * @param int $id
     * @return Response|JsonResponse
     *
     * @Route("/{entity}/{id}/note/", name="interaction_note_create")
     */
    public function noteCreateAction(Request $request, ObjectManager $om, $entity, $id)
    {
        if (!$e = $this->findEntity($entity, $id, false)) {
            throw $this->createNotFoundException();
        }

        $isProspect = $e instanceOf Prospect;
        $note = $this->instantiateClass('interaction_note');

        $form = $this->createForm(InteractionNoteType::class, $note, [
            'action' => $this->generateUrl('interaction_note_create', ['entity' => $entity, 'id' => $id])
        ]);
        $form->handleRequest($request);

        if ($form->isValid() && $form->isSubmitted()) {
            $e->addInteractionNote($note);
        
            $event = new InteractionEvent($note, Interaction::INTERACTION_TYPE_NOTE, $e);
            $this->eventDispatcher->dispatch(InteractionEvents::INTERACTION_CREATE_SUCCESS, $event);

            $om->flush();
            $this->addFlash('success', 'La note a bien été enregistrée.');

            return new JsonResponse(['redirectUrl' => $request->headers->get('referer')]);
        }

        return $this->render('@SAMCommon/Interaction/_note_form.html.twig', ['form' => $form->createView()]);
    }

    /**
     * @param Request $request
     * @param ObjectManager $om
     * @param string $entity
     * @param int $id
     *
     * @return Response|JsonResponse
     *
     * @Route("/{entity}/{entityId}/note/{id}/editer", name="interaction_note_update")
     */
    public function noteUpdateAction(Request $request, ObjectManager $om, $entity, $entityId, $id)
    {
        if (!$e = $this->findEntity($entity, $entityId)) {
            throw $this->createNotFoundException();
        }

        if (!$note = $this->findEntity('interaction_note', $id)) {
            throw $this->createNotFoundException();
        }

        if (!$this->isGranted(InteractionVoter::EDIT, $note)) {
            throw $this->createAccessDeniedException();
        }

        $form = $this->createForm(InteractionNoteType::class, $note, [
            'action' => $this->generateUrl('interaction_note_update', ['entity' => $entity, 'entityId' => $entityId, 'id' => $id])
        ]);
        $form->handleRequest($request);

        if ($form->isValid() && $form->isSubmitted()) {
            $om->persist($note);
            
            $event = new InteractionEvent($note, Interaction::INTERACTION_TYPE_NOTE, $e);
            $this->eventDispatcher->dispatch(InteractionEvents::INTERACTION_EDIT_SUCCESS, $event);

            $om->flush();
            $this->addFlash('success', 'La note a bien été enregistrée.');

            return new JsonResponse(['redirectUrl' => $request->headers->get('referer')]);
        }

        return $this->render('@SAMCommon/Interaction/_note_form.html.twig', [
            'form' => $form->createView(),
            'isProspect' => $entity === 'prospect'
        ]);
    }

    /**
     * @param Request $request
     * @param ObjectManager $om
     * @param string $entity
     * @param int $entityId
     * @param int $id
     *
     * @return Response|JsonResponse
     *
     * @Route("/{entity}/{entityId}/note/{id}/supprimer", name="interaction_note_remove")
     */
    public function noteRemoveAction(Request $request, ObjectManager $om, $entity, $entityId, $id)
    {
        if (!$e = $this->findEntity($entity, $entityId)) {
            throw $this->createNotFoundException();
        }

        if (!$note = $this->findEntity('interaction_note', $id)) {
            throw $this->createNotFoundException();
        }

        if (!$this->isGranted(InteractionVoter::REMOVE, $note)) {
            throw $this->createAccessDeniedException();
        }

        $event = new InteractionEvent($note, Interaction::INTERACTION_TYPE_NOTE, $e);
        $this->eventDispatcher->dispatch(InteractionEvents::INTERACTION_REMOVE_INITIALIZE, $event);

        $e->removeInteractionNote($note);

        $om->flush();
        $this->addFlash('success', 'La note a bien été supprimée');

        if ($request->headers->get('referer')) {
            return $this->redirect($request->headers->get('referer'));
        } else {
            return $this->generateRouteToEntity($entity, $entityId);
        }
    }

    /**
     * Generate route to entity
     * 
     * @param  string $entity   
     * @param  int $entityId 
     * @return Route           
     */
    private function generateRouteToEntity($entity, $entityId)
    {
        switch ($entity)
        {
            case 'deal_flow':
                return $this->redirectToRoute('prospect_show', ['prospect' => $entityId]);
            case 'prospect':
                return $this->redirectToRoute('show_deal_flow', ['dealFlow' => $entityId]);
            default:
                return $this->redirect('/');
        }
    }
}