<?php
/**
 * Created by PhpStorm.
 * User: yf
 * Date: 2019-04-17
 * Time: 17:39
 */

namespace GoSwoole\BaseServer\ExampleClass\Server;


use GoSwoole\BaseServer\Event\Event;
use GoSwoole\BaseServer\Event\EventDispatcher;
use GoSwoole\BaseServer\Server\Message\Message;
use GoSwoole\BaseServer\Server\Process;
use GoSwoole\BaseServer\Server\Server;
use Monolog\Logger;

class DefaultProcess extends Process
{
    private $className;

    /**
     * @var Logger
     */
    private $log;

    public function __construct(Server $server, string $groupName = self::DEFAULT_GROUP)
    {
        parent::__construct($server, $groupName);
        $this->className = get_class($this);
        $this->log = $this->context->getByClassName(Logger::class);
        //$this->log->log(Logger::INFO, "__construct");
    }

    public function onProcessStart()
    {
        $this->log = $this->context->getByClassName(Logger::class);
        $this->log->log(Logger::INFO, "onProcessStart");
        $message = new Message("message", "test");
        foreach ($this->getProcessManager()->getProcesses() as $process) {
            $this->sendMessage($message, $process);
        }
        $this->getEventDispatcher()->add("testEvent", function (Event $event) {
            $this->log->log(Logger::INFO, "[Event] {$event->getData()}");
        });
        if ($this->getProcessId() == 0) {
            sleep(1);
            $this->getEventDispatcher()->dispatchEvent(new Event("testEvent", "Hello"));
            $this->getEventDispatcher()->dispatchProcessEvent(new Event("testEvent", "Hello Every Process"), ...$this->getProcessManager()->getProcesses());
        }
    }

    public function onProcessStop()
    {
        $this->log->log(Logger::INFO, "onProcessStop");
    }

    public function onPipeMessage(Message $message, Process $fromProcess)
    {
        $this->log->log(Logger::INFO, "onPipeMessage [FromProcess:{$fromProcess->getProcessId()}] [{$message->toString()}]");
    }

    public function getEventDispatcher(): EventDispatcher
    {
        return $this->getContext()->getByClassName(EventDispatcher::class);
    }
}