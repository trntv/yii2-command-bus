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
     * @param Command $command
     * @return mixed
     */
    public function locate(Command $command);
}