<?php

namespace trntv\bus\interfaces;


/**
 * Interface SelfHandlingCommand
 *
 * @author Eugene Terentev <eugene@terentev.net>
 */
interface SelfHandlingCommand
{
    public function handle();
}