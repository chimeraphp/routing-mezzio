<?php
declare(strict_types=1);

namespace Chimera\Routing\Mezzio;

use Chimera\Routing\RouteParamsExtractor as RouteParamsExtractorInterface;
use Mezzio\Router\RouteResult;
use Psr\Http\Message\ServerRequestInterface;

final class RouteParamsExtractor implements RouteParamsExtractorInterface
{
    /**
     * {@inheritdoc}
     */
    public function getParams(ServerRequestInterface $request): array
    {
        $routeResult = $request->getAttribute(RouteResult::class);

        if (! $routeResult instanceof RouteResult) {
            return [];
        }

        return $routeResult->getMatchedParams();
    }
}
