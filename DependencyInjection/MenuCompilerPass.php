<?php

namespace WMC\MenuPartBundle\DependencyInjection;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\DependencyInjection\Definition;

class MenuCompilerPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container)
    {
        $definition = $container->getDefinition('wmc.menu_part.menu_provider');

        foreach ($container->findTaggedServiceIds('wmc.menu_part') as $id => $tags) {
            foreach ($tags as $tag) {
                if (empty($tag['menu'])) {
                    throw new \Exception("Services tagged wmc.menu_part must have a menu argument");
                }
                if (!isset($tag['priority'])) {
                    $tag['priority'] = 10;
                }

                $definition->addMethodCall('registerMenuPart', array(new Reference($id), $tag['menu'], $tag['priority']));
            }
        }
    }
}
