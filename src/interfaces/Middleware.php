<?php

namespace trntv\bus\interfaces;

/**
 * Interface Middleware
 *
 * @author Eugene Terentev <eugene@terentev.net>
 */
interface Middleware
{
    /**
     * @param            $command
     * @param callable   $next
     *
     * @return mixed
     */
    public function execute($command, callable $next);
}
