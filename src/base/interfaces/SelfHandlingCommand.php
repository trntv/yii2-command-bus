<?php

namespace trntv\bus\base\interfaces;


/**
 * Interface SelfHandlingCommand
 *
 * @author Eugene Terentev <eugene@terentev.net>
 */
interface SelfHandlingCommand
{
    public function handle();
}