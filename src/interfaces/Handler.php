<?php

namespace trntv\bus\interfaces;

/**
 * Interface Handler
 *
 * @author Eugene Terentev <eugene@terentev.net>
 */
interface Handler
{
    /**
     * @param $command
     * @return mixed
     */
    public function handle($command);
}
