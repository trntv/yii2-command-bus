<?php

namespace trntv\bus\interfaces;

interface CommandBus
{
    /**
     * @param $command
     * @return mixed
     */
    public function handle($command);
}