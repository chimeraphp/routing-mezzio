<?php
declare(strict_types=1);

namespace Chimera\Routing\Mezzio\Tests;

use Chimera\Routing\Mezzio\Application;
use Laminas\Diactoros\ResponseFactory;
use Laminas\Diactoros\ServerRequestFactory;
use Laminas\HttpHandlerRunner\Emitter\EmitterInterface;
use Laminas\HttpHandlerRunner\RequestHandlerRunner;
use Laminas\Stratigility\MiddlewarePipeInterface;
use Mezzio\Application as Mezzio;
use Mezzio\MiddlewareContainer;
use Mezzio\MiddlewareFactory;
use Mezzio\Router\RouteCollector;
use Mezzio\Router\RouterInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

/** @coversDefaultClass \Chimera\Routing\Mezzio\Application */
final class ApplicationTest extends TestCase
{
    private Mezzio $mezzio;
    private EmitterInterface&MockObject $emitter;
    private MiddlewarePipeInterface&MockObject $pipeline;

    /** @before */
    public function createDependencies(): void
    {
        $this->pipeline = $this->createMock(MiddlewarePipeInterface::class);
        $this->emitter  = $this->createMock(EmitterInterface::class);

        $this->mezzio = new Mezzio(
            new MiddlewareFactory(new MiddlewareContainer($this->createMock(ContainerInterface::class))),
            $this->pipeline,
            new RouteCollector($this->createMock(RouterInterface::class)),
            new RequestHandlerRunner(
                $this->createMock(RequestHandlerInterface::class),
                $this->emitter,
                static fn (): ServerRequestInterface => ServerRequestFactory::fromGlobals(),
                static fn (): ResponseInterface => (new ResponseFactory())->createResponse(),
            ),
        );
    }

    /**
     * @test
     *
     * @covers ::__construct
     * @covers ::handle
     */
    public function handleShouldForwardPassRequestThroughThePipeline(): void
    {
        $request  = $this->createMock(ServerRequestInterface::class);
        $response = $this->createMock(ResponseInterface::class);

        $this->pipeline->expects(self::once())
                       ->method('handle')
                       ->with($request)
                       ->willReturn($response);

        $application = new Application($this->mezzio);

        self::assertSame($response, $application->handle($request));
    }

    /**
     * @test
     *
     * @covers ::__construct
     * @covers ::run
     */
    public function runShouldInvokeApplicationRunner(): void
    {
        $this->emitter->expects(self::once())
            ->method('emit')
            ->with(self::isInstanceOf(ResponseInterface::class));

        $application = new Application($this->mezzio);
        $application->run();
    }
}
