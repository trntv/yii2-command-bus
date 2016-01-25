<?php

namespace trntv\bus\interfaces;


/**
 * Interface Middleware
 *
 * @author Eugene Terentev <eugene@terentev.net>
 */
interface Middleware
{
    public function execute($command, callable $next);
}