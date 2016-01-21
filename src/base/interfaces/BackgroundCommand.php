<?php

namespace trntv\bus\base\interfaces;

/**
 * Interface BackgroundCommand
 *
 * @author Eugene Terentev <eugene@terentev.net>
 */
interface BackgroundCommand extends Command
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