<?php
namespace Symfony\BeanstalkBundle\DependencyInjection;

use Symfony\BeanstalkBundle\AProducer;
use Symfony\BeanstalkBundle\DependencyInjection\Exception\DefinitionNotConfiguredException;
use Symfony\BeanstalkBundle\Producer;
use Symfony\BeanstalkBundle\QueueFactory;
use Pheanstalk\Pheanstalk;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Extension\Extension;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;
use Symfony\Component\DependencyInjection\Reference;

class BeanstalkExtension extends Extension
{
	/** @var ContainerBuilder */
	private $container;

	/**
	 * Loads a specific configuration.
	 *
	 * @param array $configs
	 * @param ContainerBuilder $container
	 * @throws DefinitionNotConfiguredException
	 */
	public function load(array $configs, ContainerBuilder $container)
	{
		$this->container = $container;

		$loader = new YamlFileLoader($this->container, new FileLocator(array(__DIR__ . '/../Resources/config')));
		$loader->load('services.yml');

		$configuration = new Configuration();
		$config = $this->processConfiguration($configuration, $configs);
		$this->loadQueue($config);
		$this->loadProducer();
		$this->loadErrorListener($config);
	}

	private function loadQueue(array $config)
	{
		$serviceId = 'beanstalk.queue';
		$definition = null;

		if (isset($config['processor']['dsn'])) {
			$definition = new Definition(Pheanstalk::class);
			$definition->setFactory([ QueueFactory::class, 'instanceByDsn' ]);
			$definition->setArgument('$dsn', $config['processor']['dsn']);
		}

		if (!$definition) {
			throw new DefinitionNotConfiguredException('Definition not configured by service name ' . $serviceId);
		}

		$this->container->setDefinition($serviceId, $definition);
		$this->container->setAlias(\Pheanstalk\Contract\PheanstalkInterface::class, $serviceId);
	}

	private function loadProducer()
	{
		$serviceId = 'beanstalk.producer';
		$definition = new Definition(Producer::class);
		$definition->addMethodCall('setQueue', [new Reference('beanstalk.queue')]);

		$this->container->setDefinition($serviceId, $definition);
		$this->container->setAlias(AProducer::class, $serviceId);
	}

	private function loadErrorListener(array $config)
	{
		if (!isset($config['on_error']['listener'])) return;
		$serviceId = 'beanstalk.on_error.listener';
		$definition = new Definition($config['on_error']['listener']);
		$definition->setPublic(true);
		$this->container->setDefinition($serviceId, $definition);
	}
}
