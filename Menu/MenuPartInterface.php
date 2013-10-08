<?php

namespace WMC\MenuBundle\Menu;

use Knp\Menu\MenuItem;

interface MenuPartInterface
{
    public function addMenuParts(MenuItem $menu);
}
