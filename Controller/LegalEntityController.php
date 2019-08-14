<?php

namespace SAM\CommonBundle\Controller;

use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\Routing\Annotation\Route;
use SAM\CommonBundle\Controller\Controller;
use SAM\CommonBundle\Entity\LegalEntity;
use SAM\CommonBundle\Form\Type\LegalEntityChooserType;
use SAM\CommonBundle\Manager\LegalEntityManager;
use SAM\CommonBundle\Form\Model\LegalEntityModel;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

/**
 * @Route("/vehicules")
 */
class LegalEntityController extends Controller
{
    /**
     * @param Request               $request
     * @param ObjectManager         $om
     *
     * @return Response|RedirectResponse
     *
     * @Route("/chooser", name="legal_entity_chooser", options={"expose"=true})
     */
    public function chooserAction(Request $request, LegalEntityManager $legalEntityManager)
    {
        $currentLegalEntity = $legalEntityManager->getCurrentLegalEntity();

        $countLegalEntities = $this->entityManager->getRepository('legal_entity')
            ->createQueryBuilder('le')
            ->select('COUNT(le)')
            ->getQuery()
            ->getSingleScalarResult();

        $legalEntityModel = new LegalEntityModel($currentLegalEntity);
        $form = $this->createForm(LegalEntityChooserType::class, $legalEntityModel);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $legalEntityManager->setCurrentLegalEntity($legalEntityModel->getLegalEntity());
        }

        return $this->render('@SAMCommon/LegalEntity/chooser.html.twig', [
            'form' => $form->createView(),
            'countLegalEntities' => $countLegalEntities
        ]);
    }
}
