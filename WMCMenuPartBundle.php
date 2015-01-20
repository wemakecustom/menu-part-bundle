<?php

namespace WMC\MenuPartBundle;

use Symfony\Component\HttpKernel\Bundle\Bundle;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use WMC\MenuPartBundle\DependencyInjection\MenuCompilerPass;

class WMCMenuPartBundle extends Bundle
{
    public function build(ContainerBuilder $container)
    {
        parent::build($container);

        $container->addCompilerPass(new MenuCompilerPass);
    }
}
