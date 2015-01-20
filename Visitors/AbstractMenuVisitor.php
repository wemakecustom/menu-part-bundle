<?php

namespace WMC\MenuPartBundle\Visitors;

use Knp\Menu\MenuItem;

/**
 * Depth-first default implementation for MenuVisitorInterface.
 *
 * This default implementation provides a default traversal strategy and allow
 * you to only.
 */
abstract class AbstractMenuVisitor implements MenuVisitorInterface
{
    const PROCESS_FIRST = true;
    const TRAVERSE_FIRST = false;

    private $visitBehaviour = self::PROCESS_FIRST;

    protected function traverse(MenuItem $menu)
    {
        foreach ($menu->getChildren() as $child) {
            $this->visitMenu($child);
        }
    }

    protected function getVisitBehaviour()
    {
        return $this->visitBehaviour;
    }

    protected function setVisitBehaviour($visitBehaviour)
    {
        $this->visitBehaviour = $visitBehaviour;
    }

    /**
     * {@inheritDoc}
     */
    public function visitMenu(MenuItem $menu)
    {
        if ($this->visitBehaviour === self::PROCESS_FIRST) {
            $this->visitMenuItem($menu);
            $this->traverse($menu);
        } else {
            $this->traverse($menu);
            $this->visitMenuItem($menu);
        }
    }
}
