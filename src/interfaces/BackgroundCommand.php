<?php

namespace trntv\bus\interfaces;

/**
 * Interface BackgroundCommand
 *
 * @author Eugene Terentev <eugene@terentev.net>
 */
interface BackgroundCommand
{
    /**
     * @return bool
     */
    public function isAsync();

    /**
     * @return bool
     */
    public function isRunningInBackground();
}