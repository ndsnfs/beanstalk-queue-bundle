<?php
namespace Symfony\BeanstalkBundle;

use Pheanstalk\Job;

class FileIErrorListener implements IErrorListener
{
	public function store(Job $job): bool
	{

	}
}
