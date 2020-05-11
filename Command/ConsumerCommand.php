<?php
namespace Symfony\BeanstalkBundle\Command;

use Symfony\BeanstalkBundle\AConsumer;
use Symfony\BeanstalkBundle\Events\JobNotDoneEvent;
use Symfony\BeanstalkBundle\IConfigurableConsumer;
use Symfony\BeanstalkBundle\IErrorListener;
use Pheanstalk\Contract\PheanstalkInterface;
use Psr\Container\ContainerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class ConsumerCommand extends Command
{
	/**
	 * @var ContainerInterface
	 */
	private $container;
	/**
	 * @var EventDispatcherInterface
	 */
	private $eventDispatcher;
	/**
	 * @var PheanstalkInterface
	 */
	private $queue;
	/**
	 * @var LoggerInterface
	 */
	private $logger;

	public function __construct(ContainerInterface $container, EventDispatcherInterface $eventDispatcher, PheanstalkInterface $queue, LoggerInterface $logger)
	{
		parent::__construct();
		$this->container = $container;
		$this->eventDispatcher = $eventDispatcher;
		$this->queue = $queue;
		$this->logger = $logger;
	}

	public function configure()
	{
		$this
			->setName('consumer:listen')
			->addArgument('name', InputArgument::REQUIRED, 'Consumer Name');
	}

	public function execute(InputInterface $input, OutputInterface $output)
	{
		$output->writeln('run queue');
		if ($this->container->has('beanstalk.on_error.listener')) {
			/** @var IErrorListener $errorListener */
			$errorListener = $this->container->get('beanstalk.on_error.listener');
			$this->eventDispatcher->addListener(JobNotDoneEvent::class, [ $errorListener, $errorListener::STORE_ACTION_NAME ]);
			$this->logger->debug('Added error listener ' . get_class($errorListener));
		}

		/** @var AConsumer $consumer */
		$consumer = $this->container->get($input->getArgument('name'));
		if ($consumer instanceof IConfigurableConsumer) {
			$consumer->setEventDispatcher($this->eventDispatcher);
			$consumer->setQueue($this->queue);
			$consumer->setLogger($this->logger);
		}
		$consumer->listen();
	}
}
