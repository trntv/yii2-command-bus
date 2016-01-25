<?php

namespace trntv\bus\interfaces;

/**
 * Interface Handler
 *
 * @author Eugene Terentev <eugene@terentev.net>
 */
interface Handler
{
    public function handle($command);
}