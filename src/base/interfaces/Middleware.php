<?php

namespace trntv\bus\base\interfaces;


/**
 * Interface Middleware
 *
 * @author Eugene Terentev <eugene@terentev.net>
 */
interface Middleware
{
    public function execute(Command $command, callable $next);
}