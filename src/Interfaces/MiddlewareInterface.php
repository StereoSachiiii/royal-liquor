<?php
declare(strict_types=1);

namespace Interfaces;

use Core\Request;
use Core\Response;

/**
 * Middleware Interface
 * 
 * All middleware must implement this interface
 */
interface MiddlewareInterface
{
    /**
     * Handle the request and pass to the next middleware
     *
     * @param Request $request
     * @param callable $next The next middleware in the chain
     * @return Response
     */
    public function handle(Request $request, callable $next): Response;
}
