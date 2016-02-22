<?php

/*
 * This file is part of Contao.
 *
 * Copyright (c) 2005-2015 Leo Feyer
 *
 * @license LGPL-3.0+
 */

namespace Contao\CoreBundle\EventListener;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpKernel\Event\FinishRequestEvent;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpKernel\Event\KernelEvent;

/**
 * Changes the container scope based on the route configuration.
 *
 * @author Andreas Schempp <https://github.com/aschempp>
 *
 * @deprecated Deprecated since Contao 4.2, to be removed in Contao 5.0.
 *             Use the _scope request attribute instead.
 */
class ContainerScopeListener
{
    /**
     * @var ContainerInterface
     */
    private $container;

    /**
     * Constructor.
     *
     * @param ContainerInterface $container The container object
     */
    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    /**
     * Enters the container scope when a route has been found.
     *
     * @param GetResponseEvent $event The event object
     */
    public function onKernelRequest(GetResponseEvent $event)
    {
        if (null !== ($scope = $this->getScopeFromEvent($event))) {
            $this->container->enterScope($scope);
        }
    }

    /**
     * Leaves the container scope when finishing the request.
     *
     * @param FinishRequestEvent $event The event object
     */
    public function onKernelFinishRequest(FinishRequestEvent $event)
    {
        if (null !== ($scope = $this->getScopeFromEvent($event))) {
            $this->container->leaveScope($scope);
        }
    }

    /**
     * Returns the scope from the event request.
     *
     * @param KernelEvent $event The event object
     *
     * @return string|null The scope name
     */
    private function getScopeFromEvent(KernelEvent $event)
    {
        $request = $event->getRequest();

        if (!$request->attributes->has('_scope')) {
            return null;
        }

        return $request->attributes->get('_scope');
    }
}
