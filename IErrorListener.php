<?php
namespace App\Bundle\BeanstalkBundle;

use App\Bundle\BeanstalkBundle\Events\JobNotDoneEvent;

interface IErrorListener
{
	const STORE_ACTION_NAME = 'store';

	public function store(JobNotDoneEvent $event);
}
