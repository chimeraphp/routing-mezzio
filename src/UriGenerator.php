<?php
declare(strict_types=1);

namespace Chimera\Routing\Mezzio;

use Chimera\IdentifierGenerator;
use Chimera\Routing\RouteParamsExtraction;
use Chimera\Routing\UriGenerator as UriGeneratorInterface;
use Mezzio\Router\RouterInterface;
use Psr\Http\Message\ServerRequestInterface;
use function assert;
use function is_array;

final class UriGenerator implements UriGeneratorInterface
{
    private RouterInterface $router;

    public function __construct(RouterInterface $router)
    {
        $this->router = $router;
    }

    /**
     * {@inheritdoc}
     */
    public function generateRelativePath(
        ServerRequestInterface $request,
        string $routeName,
        array $substitutions = []
    ): string {
        return $this->router->generateUri(
            $routeName,
            $this->getSubstitutions($request, $substitutions)
        );
    }

    /**
     * @param string[] $substitutions
     *
     * @return string[]
     */
    private function getSubstitutions(ServerRequestInterface $request, array $substitutions): array
    {
        $generatedId = $request->getAttribute(IdentifierGenerator::class);
        $routeParams = $request->getAttribute(RouteParamsExtraction::class);
        assert(is_array($routeParams));

        if ($generatedId !== null) {
            $substitutions['id'] = (string) $generatedId;
        }

        return $substitutions + $routeParams;
    }
}
