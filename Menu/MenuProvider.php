<?php
/**
 * @link https://github.com/KnpLabs/KnpMenuPartBundle/blob/master/Resources/doc/index.md#create-your-first-menu
 */

namespace WMC\MenuPartBundle\Menu;

use Knp\Menu\FactoryInterface;
use Knp\Menu\Provider\MenuProviderInterface;
use Knp\Menu\MenuItem;

use WMC\MenuPartBundle\Visitors\MenuVisitorInterface;

class MenuProvider implements MenuProviderInterface
{
    /**
     * @var FactoryInterface
     */
    protected $factory;

    protected $parts = array();

    protected $menus = array();

    public function __construct(FactoryInterface $factory)
    {
        $this->factory = $factory;
    }

    public function registerMenu($name, array $options = array())
    {
        $this->menus[$name] = $options;

        if (!isset($this->parts[$name])) {
            $this->parts[$name] = array();
        }
    }

    public function registerMenuPart(MenuPartInterface $part, $name, $priority = 10)
    {
        $this->parts[$name][$priority][] = $part;

        if (!isset($this->menus[$name])) {
            $this->menus[$name] = $this->menus['_all'];
        }
    }

    public static function mergeOptions(array $base, array $options)
    {
        $options = array_merge(['attributes' => [], 'class' => [], 'visitors' => []], $options);
        $base    = array_merge(['attributes' => [], 'class' => [], 'visitors' => []], $base);

        $options['attributes'] = array_merge($base['attributes'], $options['attributes']);
        $options['class']      = array_unique(array_merge($base['class'], $options['class']));
        $options['visitors']   = array_merge($base['visitors'], $options['visitors']);

        return $options;
    }

    protected static function addParts(array $index, MenuItem $menu)
    {
        ksort($index);

        foreach ($index as $priority => $parts) {
            foreach ($parts as $part) {
                $part->addMenuParts($menu);
            }
        }
    }

    protected static function runVisitors(array $visitors, MenuItem $menu)
    {
        // reindex everything to sort by priority, name
        $index = array();

        foreach ($visitors as $name => $visitor) {
            $index[$visitor['priority']][$name] = $visitor;
        }

        ksort($index);
        foreach ($index as $priority => $visitors) {
            ksort($visitors);
            foreach ($visitors as $name => $visitor) {
                $visitor['service']->visitMenu($menu);
            }
        }
    }

    public function get($name, array $options = array())
    {
        if (!$this->has($name)) {
            throw new \InvalidArgumentException(sprintf('The menu "%s" is not defined.', $name));
        }

        $options = static::mergeOptions($this->menus[$name], $options);

        $menu = $this->factory->createItem('root', array(
            'extras' => array('translation_parameters' => false),
        ));

        if (!empty($options['class'])) {
            $menu->setChildrenAttribute('class', implode(' ', $options['class']));
        }

        if (!empty($options['attributes'])) {
            foreach ($options['attributes'] as $attribute => $value) {
                $menu->setChildrenAttribute($attribute, $value);
            }
        }

        static::addParts($this->parts[$name], $menu);

        if (!empty($options['visitors'])) {
            static::runVisitors($options['visitors'], $menu);
        }

        return $menu;
    }

    public function has($name, array $options = array())
    {
        return $name !== '_all' && isset($this->menus[$name]);
    }
}
