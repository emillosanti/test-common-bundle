<?php

namespace SAM\CommonBundle\Controller;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\DependencyInjection\Exception\InvalidArgumentException;

use Symfony\Bundle\FrameworkBundle\Controller\Controller as BaseController;

class Controller extends BaseController
{
    /** @var EventDispatcherInterface */
    protected $eventDispatcher;

    /** @var EntityManagerInterface */
    protected $entityManager;

    /**
     * Controller constructor.
     * @param EventDispatcherInterface $eventDispatcher
     * @param EntityManagerInterface $entityManager
     */
    public function __construct(EventDispatcherInterface $eventDispatcher, EntityManagerInterface $entityManager)
    {
        $this->eventDispatcher = $eventDispatcher;
        $this->entityManager = $entityManager;
    }

    protected function getEntities()
    {
        return $this->container->getParameter('sam_entities');
    }

    protected function getEntityClass($key)
    {
        if (!isset($this->getEntities()[$key]))
            throw new InvalidArgumentException(sprintf('The entity was not found in sam.yml, check if entry %s exists.', $key));

        return $this->getEntities()[$key]['class'];
    }

    protected function instantiateClass($key)
    {
        if (!isset($this->getEntities()[$key]))
            throw new InvalidArgumentException(sprintf('The entity was not found in sam.yml, check if entry %s exists.', $key));

        $classType = $this->getEntities()[$key]['class'];
        return new $classType();
    }

    protected function findEntity($classKey, $identifier, $throwException = true, $message = 'Cette page n\'existe pas') 
    {
        $entity = null;
        
        if (null === $identifier || empty($identifier)) {
            if (true === $throwException) {
                throw $this->createNotFoundException($message);
            } 
        } else {
            $entity = $this->entityManager->getRepository($classKey)->find($identifier);
            if (null === $entity) {
                if (true === $throwException) {
                    throw $this->createNotFoundException($message);
                } 
            }
        }

        return $entity;
    }
    
    protected function checkCsrf($intention, $query = '_token')
    {
        $csrfProvider = $this->get('form.csrf_provider');
        $request = $this->getRequest();

        if (!$csrfProvider->isCsrfTokenValid($intention, $request->query->get($query)))
            throw new AccessDeniedHttpException('Le token CSRF n\'est pas valide.');

        return true;
    }

    protected function checkXMlHttpRequest()
    {
        if (!$this->getRequest()->isXmlHttpRequest())
            throw new HttpException(400);
    }
}