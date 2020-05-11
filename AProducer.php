<?php
namespace Symfony\BeanstalkBundle;

use Pheanstalk\Contract\PheanstalkInterface;

abstract class AProducer implements IConfigurableProducer
{
	/** @var int */
	protected $priority;
	/** @var int */
	protected $delay;
	/** @var int */
	protected $ttr;
	/** @var PheanstalkInterface */
	protected $queue;
	/** @var string */
	protected $tube;

	public function setPriority(int $priority)
	{
		$this->priority = $priority;
		return $this;
	}

	public function setDelay(int $delay)
	{
		$this->delay = $delay;
		return $this;
	}

	public function setTtr(int $ttr)
	{
		$this->ttr = $ttr;
		return $this;
	}

	public function setTube(string $tube)
	{
		$this->tube = $tube;
		return $this;
	}

	public function setQueue(PheanstalkInterface $queue)
	{
		$this->queue = $queue;
	}

	public function publish(APayload $payload): bool
	{
		$priority = $this->priority ?? PheanstalkInterface::DEFAULT_PRIORITY;
		$delay = $this->delay ?? PheanstalkInterface::DEFAULT_DELAY;
		$ttr = $this->ttr ?? PheanstalkInterface::DEFAULT_TTR;
		call_user_func_array([ $this->queue->useTube($this->tube), 'put' ], [serialize($payload), $priority, $delay, $ttr]);
		return true;
	}
}
