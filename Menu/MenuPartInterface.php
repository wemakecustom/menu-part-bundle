<?php

namespace WMC\MenuPartBundle\Menu;

use Knp\Menu\MenuItem;

interface MenuPartInterface
{
    public function addMenuParts(MenuItem $menu);
}
