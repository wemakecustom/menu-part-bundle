<?php
/**
 * @link https://github.com/KnpLabs/KnpMenuBundle/blob/master/Resources/doc/index.md#create-your-first-menu
 */

namespace WMC\MenuBundle\Menu;

use Knp\Menu\FactoryInterface;
use Knp\Menu\Provider\MenuProviderInterface;
use Knp\Menu\MenuItem;

use WMC\MenuBundle\Visitors\MenuVisitorInterface;

class MenuProvider implements MenuProviderInterface
{
    /**
     * @var FactoryInterface
     */
    protected $factory;

    protected $visitors = array();

    protected $filters = array();

    protected $parts = array();

    public function __construct(FactoryInterface $factory)
    {
        $this->factory = $factory;
    }

    public function registerMenuPart(MenuPartInterface $part, $name, $priority = 10)
    {
        $this->parts[$name][$priority][] = $part;
    }

    public function registerMenuVisitor(MenuVisitorInterface $visitor, $name = null, $priority = 10)
    {
        $this->visitors[$name][$priority][] = $visitor;
    }

    public function registerMenuFilter(MenuVisitorInterface $filter, $name = null, $priority = 10)
    {
        $this->filters[$name][$priority][] = $filter;
    }

    protected function addParts(MenuItem $menu, $name)
    {
        ksort($this->parts[$name]);

        foreach ($this->parts[$name] as $priority => $parts) {
            foreach ($parts as $part) {
                $part->addMenuParts($menu);
            }
        }
    }

    protected static function runVisitorSet($visitor_set, MenuItem $menu, $name)
    {
        $filters_comparison = function($a, $b) {
            if ($a->is_filter === $b->is_filter) {
                return 0;
            }

            return $a->is_filter ? 1 : -1;
        };

        $priorities = array_merge(array_keys(@$visitor_set[null] ?: array()),
                                  array_keys(@$visitor_set[$name] ?: array()));

        sort($priorities);

        foreach ($priorities as $priority) {
            $visitors = array_merge(@$visitor_set[null][$priority] ?: array(),
                                    @$visitor_set[$name][$priority] ?: array());

            usort($visitors, $filters_comparison);

            foreach ($visitors as $visitor) {
                $visitor->visitMenu($menu);
            }
        }
    }

    protected function visit(MenuItem $menu, $name)
    {
        MenuProvider::runVisitorSet($this->visitors, $menu, $name);
        MenuProvider::runVisitorSet($this->filters, $menu, $name);
    }

    public function get($name, array $options = array())
    {
        if (!$this->has($name)) {
            throw new \InvalidArgumentException(sprintf('The menu "%s" is not defined.', $name));
        }

        $menu = $this->factory->createItem('root');

        // This is required for Twitter Bootstrap styling
        $classes = array(
            'nav',
            "nav-$name",
            "navbar-nav",
        );
        if (!empty($options['class'])) {
            $classes[] = $options['class'];
        }
        $menu->setChildrenAttribute('class', implode(' ', $classes));

        $this->addParts($menu, $name);

        $this->visit($menu, $name);

        return $menu;
    }

    public function has($name, array $options = array())
    {
        return !empty($this->parts[$name]);
    }
}
