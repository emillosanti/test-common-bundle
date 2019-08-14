<?php

namespace SAM\CommonBundle\Controller;

use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use SAM\CommonBundle\Form\Type\DocumentType;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Class DocumentController
 *
 * @Route("/document")
 */
class DocumentController extends Controller
{
    /**
     * @param Request $request
     * @param ObjectManager $om
     * @param string $entity
     * @param int $id
     * @return Response|JsonResponse
     *
     * @Route("/{entity}/{id}", name="document_create", methods={"POST"})
     */
    public function createAction(Request $request, $entity, $id)
    {
        $data = $request->request->all();

        if (!$e = $this->findEntity($entity, $id, false)) {
            throw $this->createNotFoundException();
        }

        if (!$category = $this->entityManager->getRepository('document_category')->findOneBy(['id' => $data['category']])) {
            throw $this->createNotFoundException();
        }

        $ids = [];
        foreach ($data['files'] as $file) {
            $document = $this->instantiateClass('document');
            $document->setName($file['name']);
            $document->setUrl($file['link']);
            $document->setAuthor($this->getUser());
            $document->setCategory($category);

            $e->addDocument($document);
            $this->entityManager->flush();
            $ids[] = $document->getId();
        }

        return new JsonResponse($ids);
    }

    /**
     * @param Request $request
     * @param string $entity
     * @param int $entityId
     * @param int $id
     *
     * @return Response|JsonResponse
     *
     * @Route("/{entity}/{entityId}/{id}/supprimer", name="document_remove", methods={"DELETE"})
     */
    public function removeAction($entity, $entityId, $id)
    {
        if (!$e = $this->findEntity($entity, $entityId)) {
            throw $this->createNotFoundException();
        }

        if (!$document = $this->findEntity('document', $id)) {
            throw $this->createNotFoundException();
        }

        $e->removeDocument($document);
        $this->entityManager->flush();

        return new JsonResponse();
    }
}
