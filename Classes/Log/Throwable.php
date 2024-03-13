<?php
namespace Inoovum\Log\Throwable\Log;

use Neos\Flow\Annotations as Flow;
use Neos\Flow\Configuration\ConfigurationManager;
use Neos\Flow\Core\Bootstrap;
use Neos\Flow\Error\Debugger;
use Neos\Flow\Http\Helper\RequestInformationHelper;
use Neos\Flow\Http\HttpRequestHandlerInterface;
use Neos\Flow\Log\PlainTextFormatter;
use Neos\Flow\Log\ThrowableStorage\FileStorage;
use Neos\Flow\Log\ThrowableStorageInterface;
use Neos\Flow\ObjectManagement\ObjectManagerInterface;
use Neos\Utility\Files;
use Psr\Http\Message\RequestInterface;

/**
 * Passes detailed information about throwing objects into its own PHP class.
 */
final class Throwable extends FileStorage
{

    /**
     * Stores information about the given exception and returns information about
     * the exception and where the details have been stored. The returned message
     * can be logged or displayed as needed.
     *
     * The returned message follows this pattern:
     * Exception #<code> in <line> of <file>: <message> - See also: <dumpFilename>
     *
     * @param \Throwable $throwable
     * @param array $additionalData
     * @return string Informational message about the stored throwable
     */
    public function logThrowable(\Throwable $throwable, array $additionalData = [])
    {
        $message = $this->getErrorLogMessage($throwable);

        if ($throwable->getPrevious() !== null) {
            $additionalData['previousException'] = $this->getErrorLogMessage($throwable->getPrevious());
        }

        if (!file_exists($this->storagePath)) {
            mkdir($this->storagePath);
        }
        if (!file_exists($this->storagePath) || !is_dir($this->storagePath) || !is_writable($this->storagePath)) {
            return sprintf('Could not write exception backtrace into %s because the directory could not be created or is not writable.', $this->storagePath);
        }

        $this->cleanupThrowableDumps();

        // FIXME: getReferenceCode should probably become an interface.
        $referenceCode = (is_callable([
            $throwable,
            'getReferenceCode'
        ]) ? $throwable->getReferenceCode() : $this->generateUniqueReferenceCode());
        $throwableDumpPathAndFilename = Files::concatenatePaths([$this->storagePath, $referenceCode . '.txt']);

        $bootstrap = Bootstrap::$staticObjectManager->get(Bootstrap::class);
        /** @var ConfigurationManager $configurationManager */
        $configurationManager = $bootstrap->getEarlyInstance(ConfigurationManager::class);
        $serviceContext = $configurationManager->getConfiguration(ConfigurationManager::CONFIGURATION_TYPE_SETTINGS, 'Inoovum.Log.Throwable');

        if($serviceContext['options']['writeToFile'] === true) {
            file_put_contents($throwableDumpPathAndFilename, $this->renderErrorInfo($throwable, $additionalData));
        }
        $this->throwItIntoTheClasses($this->renderErrorInfo($throwable, $additionalData), $serviceContext['classes']);
        $message .= ' - See also: ' . basename($throwableDumpPathAndFilename);

        return $message;
    }

    /**
     * @param string $errorInfo
     * @param array $classes
     * @return void
     */
    public function throwItIntoTheClasses(string $errorInfo, array $classes): void
    {
        $bootstrap = Bootstrap::$staticObjectManager->get(Bootstrap::class);
        /** @var ObjectManagerInterface $objectManager */
        $objectManager = $bootstrap->getEarlyInstance(ObjectManagerInterface::class);

        foreach ($classes as $throwableClass) {
            $targetClass = $objectManager->get($throwableClass['class']);
            $targetClass->throwError($errorInfo, array_key_exists('options', $throwableClass) ? $throwableClass['options'] : []);
        }
    }

}
