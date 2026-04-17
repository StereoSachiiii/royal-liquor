<?php
declare(strict_types=1);

namespace App\DIContainer;

/**
 * Base ServiceProvider class
 */
abstract class ServiceProvider
{
    /**
     * @var Container
     */
    protected Container $container;

    /**
     * @param Container $container
     */
    public function __construct(Container $container)
    {
        $this->container = $container;
    }

    /**
     * Register services in the container
     */
    abstract public function register(): void;

    /**
     * Boot the services (optional)
     */
    public function boot(): void
    {
        // Optional boot logic
    }
}
