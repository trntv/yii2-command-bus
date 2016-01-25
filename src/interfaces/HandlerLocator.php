<?php

namespace trntv\bus\interfaces;

/**
 * Interface HandlerLocator
 *
 * @author Eugene Terentev <eugene@terentev.net>
 */
interface HandlerLocator
{
    /**
     * @param $command
     * @return mixed
     */
    public function locate($command);
}