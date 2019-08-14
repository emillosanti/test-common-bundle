<?php

namespace SAM\CommonBundle\Admin;

use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Route\RouteCollection;

class ErrorAdmin extends AbstractAdmin
{
    protected $baseRouteName = 'sonata_tool_error';
    protected $baseRoutePattern = 'tool-error';

    protected function configureRoutes(RouteCollection $collection)
    {
        $collection->clearExcept(['list']);
    }
}
