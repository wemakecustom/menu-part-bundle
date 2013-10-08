<?php

namespace WMC\MenuBundle\Visitors;

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

    private $process_first;

    public function __construct($visit_behaviour = AbstractMenuVisitor::PROCESS_FIRST)
    {
        $this->process_first = $visit_behaviour;
    }

    protected function traverse(MenuItem $menu)
    {
        foreach ($menu->getChildren() as $child) {
            $this->visitMenu($child);
        }
    }

    final protected function getVisitBehaviour()
    {
        return $this->process_first;
    }

    /**
     * {@inheritDoc}
     */
    final public function visitMenu(MenuItem $menu)
    {
        if ($this->process_first) {
            $this->visitMenuItem($menu);
            $this->traverse($menu);
        } else {
            $this->traverse($menu);
            $this->visitMenuItem($menu);
        }
    }
}
