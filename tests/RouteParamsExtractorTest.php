<?php
declare(strict_types=1);

namespace Chimera\Routing\Mezzio\Tests;

use Chimera\Routing\Mezzio\RouteParamsExtractor;
use Laminas\Diactoros\ServerRequest;
use Mezzio\Router\Route;
use Mezzio\Router\RouteResult;
use PHPUnit\Framework\TestCase;

/** @coversDefaultClass \Chimera\Routing\Mezzio\RouteParamsExtractor */
final class RouteParamsExtractorTest extends TestCase
{
    /**
     * @test
     *
     * @covers ::getParams()
     */
    public function getParamsShouldReturnAnEmptyArrayWhenAttributeWasNotConfigured(): void
    {
        $extractor = new RouteParamsExtractor();
        $request   = new ServerRequest();

        self::assertSame([], $extractor->getParams($request));
    }

    /**
     * @test
     *
     * @covers ::getParams()
     */
    public function getParamsShouldRetrieveRouteParamsFromTheAttributeConfiguredByTheRoutingMiddleware(): void
    {
        $routeResult = RouteResult::fromRoute(
            $this->createMock(Route::class),
            ['test' => '1']
        );

        $extractor = new RouteParamsExtractor();
        $request   = (new ServerRequest())->withAttribute(RouteResult::class, $routeResult);

        self::assertSame(['test' => '1'], $extractor->getParams($request));
    }
}
