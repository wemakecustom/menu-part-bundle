<?php

// src/Acme/DemoBundle/Menu/HomeMenu.php

namespace Acme\DemoBundle\Menu;

use WMC\MenuPartBundle\Menu\MenuPartInterface;
use Knp\Menu\MenuItem;

class HomeMenu implements MenuPartInterface
{
    public function addMenuParts(MenuItem $menu)
        $menu->addChild(
            'Home',
            array(
                'route' => 'homepage',
            )
        );
    }
}
