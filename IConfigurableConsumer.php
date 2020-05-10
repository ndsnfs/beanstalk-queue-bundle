<?php
namespace App\Bundle\BeanstalkBundle;

use Pheanstalk\Contract\PheanstalkInterface;
use Psr\Log\LoggerInterface;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

interface IConfigurableConsumer
{
	public function setEventDispatcher(EventDispatcherInterface $eventDispatcher);
	public function setQueue(PheanstalkInterface $queue);
	public function setLogger(LoggerInterface $logger);
}
