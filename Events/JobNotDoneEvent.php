<?php
namespace App\Bundle\BeanstalkBundle\Events;

use Pheanstalk\Job;
use Symfony\Contracts\EventDispatcher\Event;

class JobNotDoneEvent extends Event
{
	/** @var Job */
	private $job;

	public function __construct(Job $job)
	{
		$this->job = $job;
	}

	public function getJob(): Job
	{
		return $this->job;
	}
}
