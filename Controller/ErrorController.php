<?php

namespace SAM\CommonBundle\Controller;

use Symfony\Component\Routing\Annotation\Route;
use SAM\CommonBundle\Controller\Controller;

/**
 * @Route("/errors", options = { "expose" = true })
 */
class ErrorController extends Controller
{
    /**
     * @Route("/side/403", name="errors_side_403")
     */
    public function showSide403()
    {
        return $this->render('@SAMCommon/Error/side-403.html.twig');
    }

    /**
     * @Route("/side/5xx", name="errors_side_5xx")
     */
    public function showSideGeneric()
    {
        return $this->render('@SAMCommon/Error/side-generic.html.twig');
    }
}
