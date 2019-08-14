<?php

namespace SAM\CommonBundle\Controller;

use Sonata\AdminBundle\Controller\CRUDController;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\Request;

class ErrorCRUDController extends CRUDController
{
    /** @var LoggerInterface */
    protected $logger;

    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    public function listAction()
    {
        $request = $this->getRequest();
        if ($request->isMethod('post')) {
            $message = '[TEST] ' . $request->request->get('message');
            $context = [ 
                'context' => $request->attributes->get('_controller'),
                'module' => 'tools.logger',
                'cause' => 'test'
            ];

            switch ($request->request->get('level')) {
                case 'emergency':
                    $this->logger->emergency($message, $context);
                    break;
                case 'alert':
                    $this->logger->alert($message, $context);
                    break;
                case 'critical':
                    $this->logger->critical($message, $context);
                    break;
                case 'error':
                    $this->logger->error($message, $context);
                    break;
                case 'warning':
                    $this->logger->warning($message, $context);
                    break;
                case 'notice':
                    $this->logger->notice($message, $context);
                    break;
                case 'info':
                    $this->logger->info($message, $context);
                    break;
                case 'debug':
                    $this->logger->debug($message, $context);
                    break;
                case 'log':
                    $this->logger->log($message, $context);
                    break;
            }
        }

        return $this->renderWithExtraParams('@SAMCommon/Admin/Tools/logger.html.twig');
    }
}
