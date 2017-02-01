<?php

namespace trntv\bus\middlewares;

use Symfony\Component\Process\Process;
use trntv\bus\interfaces\BackgroundCommand;
use trntv\bus\interfaces\Middleware;
use Yii;
use yii\base\Object;

/**
 * Class BackgroundCommandMiddleware
 * @package trntv\bus\middlewares
 * @author Eugene Terentev <eugene@terentev.net>
 */
class BackgroundCommandMiddleware extends Object implements Middleware
{
    /**
     * @var string Path to php executable
     */
    public $backgroundHandlerBinary;
    /**
     * @var string Path to cli script
     */
    public $backgroundHandlerPath;
    /**
     * @var string console route
     */
    public $backgroundHandlerRoute;
    /**
     * @var int|float|null process timeout
     */
    public $backgroundProcessTimeout = 60;
    /**
     * @var int|float|null process idle timeout
     */
    public $backgroundProcessIdleTimeout;
    /**
     * @var array Arguments that will be passed to script
     */
    public $backgroundHandlerArguments = [];
    /**
     * @var array Arguments that will be passed to binary
     *
     * ```php
     *      'backgroundHandlerPath' => '/path/to/script.php',
     *      'backgroundHandlerArguments' => ['--foo bar']
     *      'backgroundHandlerBinaryArguments' => ['--define memory_limit=1G']
     * ```
     * will generate
     * ```
     * php --define memory_limit=1G /path/to/script.php --foo bar
     * ```
     */
    public $backgroundHandlerBinaryArguments = [];

    public function execute($command, callable $next)
    {

        if ($command instanceof BackgroundCommand && !$command->isRunningInBackground()) {
            return $this->runProcess($command);
        }

        return $next($command);
    }

    /**
     * @param BackgroundCommand $command
     * @return string
     */
    protected function runProcess(BackgroundCommand $command)
    {
        $binary = $this->getBackgroundHandlerBinary();
        $path = $this->getBackgroundHandlerPath();
        $route = $this->getBackgroundHandlerRoute();
        $arguments = implode(' ', $this->getBackgroundHandlerArguments($command));
        $binaryArguments = implode(' ', $this->backgroundHandlerBinaryArguments);

        $process = new Process("{$binary} {$binaryArguments} {$path} {$route} {$arguments}");
        $process->setTimeout($this->backgroundProcessTimeout);
        $process->setIdleTimeout($this->backgroundProcessIdleTimeout);
        if ($command->isAsync()) {
            $process->start();
        } else {
            $process->run();
        }

        return $process;
    }

    /**
     * @return bool|string
     */
    public function getBackgroundHandlerBinary()
    {
        // Only return PHP_BINARY if it's set and it's in PHP_BINDIR
        if ($this->backgroundHandlerBinary) {
            $binary = $this->backgroundHandlerBinary;
        } elseif (defined('PHP_BINARY') && PHP_BINARY != '' && dirname(PHP_BINARY) == PHP_BINDIR) {
            $binary = PHP_BINARY;
        } else {
            $environmentPaths = explode(PATH_SEPARATOR, getenv('PATH'));
            $environmentPaths[] = PHP_BINDIR;
            foreach ($environmentPaths as $path) {
                $path = rtrim(str_replace('\\', '/', $path), '/');
                if (strlen($path) == 0) {
                    continue;
                }
                $binary = $path . '/php' . (DIRECTORY_SEPARATOR !== '/' ? '.exe' : '');
                $binary = $this->checkPhpBinary($binary) ? $binary : '';
            }
        }

        return isset($binary) ? Yii::getAlias($binary) : '';
    }

    /**
     * Checks if the given PHP binary is executable and of the same version as the currently running one.
     *
     * @param string $binary
     *
     * @return bool
     */
    protected function checkPhpBinary($binary)
    {
        $phpVersion = null;
        if (file_exists($binary) && is_file($binary)) {
            $phpVersion = trim(exec(escapeshellcmd($binary) . ' -r "echo PHP_VERSION;"'));
            if ($phpVersion === PHP_VERSION) {
                return true;
            }
        }

        return false;
    }

    /**
     * @return bool|string
     */
    public function getBackgroundHandlerPath()
    {
        return Yii::getAlias($this->backgroundHandlerPath);
    }

    /**
     * @return mixed
     */
    public function getBackgroundHandlerRoute()
    {
        return $this->backgroundHandlerRoute;
    }

    /**
     * @param $command
     * @return array
     */
    private function getBackgroundHandlerArguments($command)
    {
        $arguments = $this->backgroundHandlerArguments;
        $command = base64_encode(serialize($command));
        array_unshift($arguments, $command);
        return $arguments;
    }
}
