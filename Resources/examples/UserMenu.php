<?php

// src/Acme/DemoBundle/Menu/UserMenu.php

namespace Acme\DemoBundle\Menu;

use WMC\MenuPartBundle\Menu\MenuPartInterface;
use JMS\DiExtraBundle\Annotation\Service;
use JMS\DiExtraBundle\Annotation\Tag;
use JMS\DiExtraBundle\Annotation\Inject;
use Knp\Menu\MenuItem;

/**
 * @Service(public=false)
 * @Tag("wmc.menu_part", attributes={"menu" = "user"})
 */
class UserMenu implements MenuPartInterface
{
    /**
     * @Inject("security.context")
     */
    public $securityContext;

    public function addMenuParts(MenuItem $menu)
    {
        $user = $this->securityContext->getToken()->getUser();

        if (is_object($user)) {
            $menu->addChild(
                'Profile',
                array(
                    'route' => 'fos_user_profile_show',
                    'routeParameters' => array('id' => $user->getId()),
                )
            );
        }
    }
}
