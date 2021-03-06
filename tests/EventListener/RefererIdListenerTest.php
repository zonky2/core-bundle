<?php

declare(strict_types=1);

/*
 * This file is part of Contao.
 *
 * (c) Leo Feyer
 *
 * @license LGPL-3.0-or-later
 */

namespace Contao\CoreBundle\Tests\EventListener;

use Contao\CoreBundle\ContaoCoreBundle;
use Contao\CoreBundle\EventListener\RefererIdListener;
use Contao\CoreBundle\Tests\TestCase;
use PHPUnit\Framework\MockObject\MockObject;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\Security\Csrf\TokenGenerator\TokenGeneratorInterface;

class RefererIdListenerTest extends TestCase
{
    public function testAddsTheTokenToTheRequest(): void
    {
        $request = new Request();
        $request->attributes->set('_scope', ContaoCoreBundle::SCOPE_BACKEND);

        $kernel = $this->createMock(KernelInterface::class);
        $event = new GetResponseEvent($kernel, $request, HttpKernelInterface::MASTER_REQUEST);

        $listener = new RefererIdListener($this->mockTokenGenerator(), $this->mockScopeMatcher());
        $listener->onKernelRequest($event);

        $this->assertTrue($request->attributes->has('_contao_referer_id'));
        $this->assertSame('testValue', $request->attributes->get('_contao_referer_id'));
    }

    public function testDoesNotAddTheTokenInFrontEndScope(): void
    {
        $request = new Request();
        $request->attributes->set('_scope', ContaoCoreBundle::SCOPE_FRONTEND);

        $kernel = $this->createMock(KernelInterface::class);
        $event = new GetResponseEvent($kernel, $request, HttpKernelInterface::MASTER_REQUEST);

        $listener = new RefererIdListener($this->mockTokenGenerator(), $this->mockScopeMatcher());
        $listener->onKernelRequest($event);

        $this->assertFalse($request->attributes->has('_contao_referer_id'));
    }

    public function testDoesNotAddTheTokenToASubrequest(): void
    {
        $request = new Request();
        $request->attributes->set('_scope', ContaoCoreBundle::SCOPE_BACKEND);

        $kernel = $this->createMock(KernelInterface::class);
        $event = new GetResponseEvent($kernel, $request, HttpKernelInterface::SUB_REQUEST);

        $listener = new RefererIdListener($this->mockTokenGenerator(), $this->mockScopeMatcher());
        $listener->onKernelRequest($event);

        $this->assertFalse($request->attributes->has('_contao_referer_id'));
    }

    public function testAddsTheSameTokenToSubsequestRequests(): void
    {
        $request = new Request();
        $request->attributes->set('_scope', ContaoCoreBundle::SCOPE_BACKEND);

        $kernel = $this->createMock(KernelInterface::class);
        $event = new GetResponseEvent($kernel, $request, HttpKernelInterface::MASTER_REQUEST);

        $listener = new RefererIdListener($this->mockTokenGenerator(), $this->mockScopeMatcher());
        $listener->onKernelRequest($event);

        $this->assertTrue($request->attributes->has('_contao_referer_id'));
        $this->assertSame('testValue', $request->attributes->get('_contao_referer_id'));

        $listener->onKernelRequest($event);

        $this->assertTrue($request->attributes->has('_contao_referer_id'));
        $this->assertSame('testValue', $request->attributes->get('_contao_referer_id'));
    }

    /**
     * @return TokenGeneratorInterface&MockObject
     */
    private function mockTokenGenerator(): TokenGeneratorInterface
    {
        $tokenGenerator = $this->createMock(TokenGeneratorInterface::class);
        $tokenGenerator
            ->method('generateToken')
            ->willReturn('testValue')
        ;

        return $tokenGenerator;
    }
}
