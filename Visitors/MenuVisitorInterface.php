<?php

namespace WMC\MenuBundle\Visitors;

use Knp\Menu\MenuItem;

interface MenuVisitorInterface
{
    /**
     * This function is intended to control the menu traversal (child first,
     * parent first, only two first layers, etc.).
     *
     * It is expected for this method to call visitMenuItem to delegate the
     * actual node processing.
     *
     * It is therefore expected to be used as the Visitor entry point.
     */
    public function visitMenu(MenuItem $menu);

    /**
     * This function is intended to actually process the node and should not
     * provide any traversing behaviour.
     *
     * It is expected for this method to be called internally from visitMenu and
     * to return control in order to continue menu traversal.
     */
    public function visitMenuItem(MenuItem $item);
}
