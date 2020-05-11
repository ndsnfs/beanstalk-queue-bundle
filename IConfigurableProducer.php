<?php
namespace Symfony\BeanstalkBundle;

use Pheanstalk\Contract\PheanstalkInterface;

interface IConfigurableProducer
{
	public function setQueue(PheanstalkInterface $queue);
}
