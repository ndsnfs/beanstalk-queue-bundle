<?php
namespace Symfony\BeanstalkBundle;

use Pheanstalk\Pheanstalk;

class QueueFactory
{
	public static function instanceByDsn(string $dsn)
	{
		$parsed = parse_url($dsn);
		return Pheanstalk::create($parsed['host'], $parsed['port']);
	}
}
