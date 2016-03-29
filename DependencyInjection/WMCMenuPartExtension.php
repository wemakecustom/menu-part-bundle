<?php

namespace WMC\MenuPartBundle\DependencyInjection;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\HttpKernel\DependencyInjection\ConfigurableExtension;
use Symfony\Component\DependencyInjection\Loader;
use Symfony\Component\DependencyInjection\Reference;

/**
 * This is the class that loads and manages your bundle configuration
 *
 * To learn more see {@link http://symfony.com/doc/current/cookbook/bundles/extension.html}
 */
class WMCMenuPartExtension extends ConfigurableExtension
{
    protected function loadInternal(array $mergedConfigs, ContainerBuilder $container)
    {
        $loader = new Loader\XmlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('services.xml');

        $definition = $container->getDefinition('wmc.menu_part.menu_provider');

        foreach ($mergedConfigs['menus'] as $menu => $options) {
            if (isset($options['visitors'])) {
               foreach ($options['visitors'] as $visitorId => &$visitor) {
                  $visitor['service'] = new Reference($visitorId);
               }
            }
            $definition->addMethodCall('registerMenu', array($menu, $options));
        }
    }
}
