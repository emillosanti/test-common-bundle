<?php

namespace SAM\CommonBundle\Controller;

use Sonata\AdminBundle\Controller\CRUDController;
use SAM\CommonBundle\Manager\MailManager;
use Symfony\Component\HttpFoundation\Request;

class EmailCRUDController extends CRUDController
{
    /**
     * @var MailManager
     */
    private $mailManager;

    public function __construct(MailManager $mailManager)
    {
        $this->mailManager = $mailManager;
    }

    public function listAction()
    {
        $request = $this->getRequest();

        if ($request->isMethod('post')) {
            $this->mailManager->sendMessage($this->mailManager->getSender(), $request->request->get('to'), $request->request->get('subject'), '<p>Ceci est un test</p>');
        }

        return $this->renderWithExtraParams('@SAMCommon/Admin/Tools/emails.html.twig');
    }
}
