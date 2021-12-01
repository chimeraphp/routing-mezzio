<?php
declare(strict_types=1);

namespace Chimera\Routing\Mezzio;

use Chimera\Routing\Application as ApplicationInterface;
use Mezzio\Application as Mezzio;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

final class Application implements ApplicationInterface
{
    public function __construct(private Mezzio $application)
    {
    }

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        return $this->application->handle($request);
    }

    public function run(): void
    {
        $this->application->run();
    }
}
