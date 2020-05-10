<?php
namespace App\Bundle\BeanstalkBundle\DependencyInjection;


use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class Configuration implements ConfigurationInterface
{
	/**
	 * Generates the configuration tree builder.
	 *
	 * @return \Symfony\Component\Config\Definition\Builder\TreeBuilder The tree builder
	 */
	public function getConfigTreeBuilder()
	{
		$builder = new TreeBuilder('beanstalk');
		$builder->getRootNode()
			->children()
				->arrayNode('processor')
					->children()
						->scalarNode('dsn')->end()
					->end()
				->end()
				->arrayNode('on_error')
					->children()
						->scalarNode('listener')->end()
					->end()
				->end()
			->end();

		return $builder;
	}
}