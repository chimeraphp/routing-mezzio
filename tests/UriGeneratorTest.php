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
    /** @var RouterInterface|MockObject */
    private $router;

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
     * @param string[] $substitutions
     * @param string[] $expectedSubstitutions
     */
    public function generateRelativePathShouldCallTheRouterToGeneratePaths(
        ServerRequestInterface $request,
        array $substitutions,
        array $expectedSubstitutions
    ): void {
        $generator = new UriGenerator($this->router);

        $this->router->expects(self::once())
                     ->method('generateUri')
                     ->with('test', self::identicalTo($expectedSubstitutions))
                     ->willReturn('/test');

        self::assertSame('/test', $generator->generateRelativePath($request, 'test', $substitutions));
    }

    /** @return array<string, array<int, ServerRequest|array<string, string>>> */
    public function possibleScenarios(): array
    {
        return [
            'no data at all'      => [
                (new ServerRequest())->withAttribute(RouteParamsExtraction::class, []),
                [],
                [],
            ],
            'route args only'     => [
                (new ServerRequest())->withAttribute(RouteParamsExtraction::class, ['test' => '1']),
                [],
                ['test' => '1'],
            ],
            'route args + subs'   => [
                (new ServerRequest())->withAttribute(RouteParamsExtraction::class, ['test' => '2', 'a' => '1']),
                ['test' => '1'],
                ['test' => '1', 'a' => '1'],
            ],
            'id only'             => [
                (new ServerRequest())->withAttribute(RouteParamsExtraction::class, [])
                                     ->withAttribute(IdentifierGenerator::class, 1),
                [],
                ['id' => '1'],
            ],
            'id only + subs'      => [
                (new ServerRequest())->withAttribute(RouteParamsExtraction::class, [])
                                     ->withAttribute(IdentifierGenerator::class, 1),
                ['test' => '1', 'id' => '1234'],
                ['test' => '1', 'id' => '1'],
            ],
            'everything together' => [
                (new ServerRequest())->withAttribute(RouteParamsExtraction::class, ['test' => '2', 'a' => '1'])
                                     ->withAttribute(IdentifierGenerator::class, 1),
                ['test' => '1', 'id' => '1234'],
                ['test' => '1', 'id' => '1', 'a' => '1'],
            ],
        ];
    }
}
