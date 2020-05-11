<?php
namespace Symfony\BeanstalkBundle;

use Symfony\BeanstalkBundle\Events\JobNotDoneEvent;

interface IErrorListener
{
	const STORE_ACTION_NAME = 'store';

	public function store(JobNotDoneEvent $event);
}
