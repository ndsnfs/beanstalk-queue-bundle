<?php
namespace App\Bundle\BeanstalkBundle;

use App\Bundle\BeanstalkBundle\Events\JobNotDoneEvent;
use App\Bundle\BeanstalkBundle\Exception\WrongPayloadException;
use Pheanstalk\Contract\PheanstalkInterface;
use Pheanstalk\Job;
use Psr\Log\LoggerInterface;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

abstract class AConsumer implements IConfigurableConsumer
{
	protected $maxHandleJobCnt = 10;
	protected $waitTimeout = 50;
	/**
	 * @var PheanstalkInterface
	 */
	private $queue;
	/**
	 * @var EventDispatcherInterface
	 */
	private $eventDispatcher;
	/**
	 * @var LoggerInterface
	 */
	private $logger;

	public function setEventDispatcher(EventDispatcherInterface $eventDispatcher)
	{
		$this->eventDispatcher = $eventDispatcher;
	}

	public function setQueue(PheanstalkInterface $queue)
	{
		$this->queue = $queue;
	}

	public function setLogger(LoggerInterface $logger)
	{
		$this->logger = $logger;
	}

	abstract public function execute(Job $carrier);

	abstract public function tubeName() : string;

	public function listen()
	{
		while($this->maxHandleJobCnt) {
			print 'watch job ...' . PHP_EOL;
			$job = $this->queue->watch($this->tubeName())->ignore('default')->reserveWithTimeout($this->waitTimeout);
			if (!$job) continue;

			try {
				$this->execute($job);
				print 'job handled' . PHP_EOL;
				$this->queue->delete($job);
			} catch (\Throwable $exception) {
				$this->queue->bury($job);
				print 'job bury' . PHP_EOL;
				$this->eventDispatcher->dispatch(new JobNotDoneEvent($job));
				$this->logger->warning('Job not done in tube ' . $this->tubeName() . '. Message: ' . $exception->getMessage() . '. File: ' . $exception->getFile() . '. Line: ' . $exception->getLine());
			}

			$this->maxHandleJobCnt--;
		}
	}

	/**
	 * @param Job $job
	 * @param string $asPayloadClass
	 * @return APayload
	 * @throws WrongPayloadException
	 */
	protected function extract(Job $job, string $asPayloadClass)
	{
		$payload = unserialize($job->getData());

		if (!($payload instanceof $asPayloadClass)) {
			throw new WrongPayloadException('Payload must implement ' . $asPayloadClass);
		}

		return $payload;
	}
}
