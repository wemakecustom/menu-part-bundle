<?php

namespace WMC\MenuBundle\DependencyInjection;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\DependencyInjection\Definition;

class MenuCompilerPass implements CompilerPassInterface
{
    private $container;
    private $menu_provider_definition;

    public function process(ContainerBuilder $container)
    {
        $this->container = $container;
        $this->menu_provider_definition = $container->getDefinition('wmc.menu.menu_provider');
        $menus = array();
        $visitors = array();

        foreach ($container->findTaggedServiceIds('wmc.menu.part') as $id => $tags) {
            foreach ($tags as $tag) {
                if (empty($tag['menu'])) {
                    throw new \Exception("Services tagged wmc.menu.part must have a menu argument");
                }
                if (!isset($tag['priority'])) {
                    $tag['priority'] = 10;
                }
                $menus[] = $tag['menu'];
                $this->menu_provider_definition->addMethodCall('registerMenuPart', array(new Reference($id), $tag['menu'], $tag['priority']));
            }
        }

        $this->registerMenuExtensions('visitor', 'registerMenuVisitor');
        $this->registerMenuExtensions('filter', 'registerMenuFilter');

        foreach (array_unique($menus) as $menu) {
            $definition = new Definition('Knp\Menu\MenuItem');
            // $definition->setScope('request');
            $definition->setFactoryService('wmc.menu.menu_provider');
            $definition->setFactoryMethod('get');
            $definition->setArguments(array($menu));

            $container->setDefinition("wmc.menu.$menu", $definition);
        }
    }

    protected function registerMenuExtensions($tag_name, $register_method)
    {
        foreach ($this->container->findTaggedServiceIds('wmc.menu.'.$tag_name) as $id => $tags) {
            foreach ($tags as $tag) {
                if (!isset($tag['priority'])) {
                    $tag['priority'] = 10;
                }
                if (!isset($tag['menu'])) {
                    $tag['menu'] = null;
                }
                $this->menu_provider_definition->addMethodCall($register_method, array(new Reference($id), $tag['menu'], $tag['priority']));
            }
        }
    }
}
