<?php

namespace trntv\bus\base\interfaces;

/**
 * Interface Handler
 *
 * @author Eugene Terentev <eugene@terentev.net>
 */
interface Handler
{
    public function handle(Command $command);
}