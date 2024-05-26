<?php
namespace Inoovum\Log\Throwable\Log;

/**
 * Throwable Interace
 */
interface ThrowableInterface
{

    /**
     * @param string $errorInfo
     * @param array $options
     * @return void
     */
    public function throwError(string $errorInfo, array $options): void;

}
