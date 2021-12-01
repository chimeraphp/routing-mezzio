<?php
declare(strict_types=1);

namespace Chimera\Routing\Mezzio\Tests;

use Chimera\IdentifierGenerator;
use Chimera\Routing\Mezzio\UriGenerator;
use Chimera\Routing\RouteParamsExtraction;
use Laminas\Diactoros\ServerRequest;
use Mezzio\Router\RouterInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ServerRequestInterface;

/** @coversDefaultClass \Chimera\Routing\Mezzio\UriGenerator */
final class UriGeneratorTest extends TestCase
{
    /** @var RouterInterface&MockObject */
    private RouterInterface $router;

    /** @before */
    public function configureRouter(): void
    {
        $this->router = $this->createMock(RouterInterface::class);
    }

    /**
     * @test
     * @dataProvider possibleScenarios
     *
     * @covers ::__construct()
     * @covers ::generateRelativePath()
     * @covers ::getSubstitutions()
     *
     * @param array<string, string> $substitutions
     * @param array<string, string> $expectedSubstitutions
     */
    public function generateRelativePathShouldCallTheRouterToGeneratePaths(
        ServerRequestInterface $request,
        array $substitutions,
        array $expectedSubstitutions,
    ): void {
        $generator = new UriGenerator($this->router);

        $this->router->expects(self::once())
                     ->method('generateUri')
                     ->with('test', self::identicalTo($expectedSubstitutions))
                     ->willReturn('/test');

        self::assertSame('/test', $generator->generateRelativePath($request, 'test', $substitutions));
    }

    /** @return iterable<string, array{ServerRequestInterface, array<string, string>, array<string, string>}> */
    public function possibleScenarios(): iterable
    {
        yield 'no data at all' => [
            (new ServerRequest())->withAttribute(RouteParamsExtraction::class, []),
            [],
            [],
        ];

        yield 'route args only' => [
            (new ServerRequest())->withAttribute(RouteParamsExtraction::class, ['test' => '1']),
            [],
            ['test' => '1'],
        ];

        yield 'route args + subs' => [
            (new ServerRequest())->withAttribute(RouteParamsExtraction::class, ['test' => '2', 'a' => '1']),
            ['test' => '1'],
            ['test' => '1', 'a' => '1'],
        ];

        yield 'id only' => [
            (new ServerRequest())->withAttribute(RouteParamsExtraction::class, [])
                                 ->withAttribute(IdentifierGenerator::class, 1),
            [],
            ['id' => '1'],
        ];

        yield 'id only + subs' => [
            (new ServerRequest())->withAttribute(RouteParamsExtraction::class, [])
                                 ->withAttribute(IdentifierGenerator::class, 1),
            ['test' => '1', 'id' => '1234'],
            ['test' => '1', 'id' => '1'],
        ];

        yield 'everything together' => [
            (new ServerRequest())->withAttribute(RouteParamsExtraction::class, ['test' => '2', 'a' => '1'])
                                 ->withAttribute(IdentifierGenerator::class, 1),
            ['test' => '1', 'id' => '1234'],
            ['test' => '1', 'id' => '1', 'a' => '1'],
        ];
    }
}
