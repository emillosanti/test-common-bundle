<?php

namespace SAM\CommonBundle\Admin;

use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Route\RouteCollection;

class EmailAdmin extends AbstractAdmin
{
    protected $baseRouteName = 'sonata_tool_email';
    protected $baseRoutePattern = 'tool-email';

    protected function configureRoutes(RouteCollection $collection)
    {
        $collection->clearExcept(['list']);
    }
}
