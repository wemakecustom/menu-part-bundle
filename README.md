# WMCMenuPartBundle

Wrapper around [`knplabs/knp-menu-bundle`](https://github.com/KnpLabs/KnpMenuBundle)
to easily create partial self contained menu providers.

The goal is to create global menus (main, top, sidebar, etc.) and have different services fill them when they see fit.
An example would be a sidebar that offers actions (edit, delete, etc.) on the current resource.

This bundle really needs tests though…

**WARNING**: This bundles requries KnpMenuBundle **~2.0**.

## Installation

Download and install the bundle via composer

``` bash
$ php composer.phar require wemakecustom/menu-part-bundle
```

Enable the Bundle (and its dependencies) in the Kernel:

```php
<?php
// app/AppKernel.php

public function registerBundles()
{
    $bundles = array(
        // ...
        new Knp\Bundle\MenuBundle\KnpMenuBundle(),
        new WMC\MenuPartBundle\WMCMenuPartBundle(),
    );
}
```

## Creating your first menu

Using [`JMSDiExtraBundle`](https://github.com/schmittjoh/JMSDiExtraBundle),
you can simply create a class that extends [`MenuPartInterface`](Menu/MenuPartInterface.php):

```php
<?php
// src/Acme/DemoBundle/Menu/UserMenu.php

namespace Acme\DemoBundle\Menu;

use WMC\MenuPartBundle\Menu\MenuPartInterface;
use JMS\DiExtraBundle\Annotation\Service;
use JMS\DiExtraBundle\Annotation\Tag;
use JMS\DiExtraBundle\Annotation\Inject;
use JMS\DiExtraBundle\Annotation\InjectParams;
use Knp\Menu\MenuItem;

use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

/**
 * @Service(public=false)
 * @Tag("wmc.menu_part", attributes={"menu" = "user"})
 */
class UserMenu implements MenuPartInterface
{
    protected $authorizationChecker;

    /**
     * @InjectParams({
     *     "authorizationChecker" = @Inject("security.authorization_checker")
     * })
     */
    public function __construct(AuthorizationCheckerInterface $authorizationChecker)
    {
        $this->authorizationChecker = $authorizationChecker;
    }

    public function addMenuParts(MenuItem $menu)
    {
        if ($this->authorizationChecker->isGranted('IS_AUTHENTICATED_REMEMBERED')) {
            $menu->addChild('Profile', array('route' => 'fos_user_profile_show'));
        }
    }
}
?>
```

Notice the `menu` attribute set to `user`, this is mandatory.
You can create multiple `MenuPartInterface` with the same menu name and they will
concatenante themselves. You can also use the `priority` tag attribute to modify the order.

This is a service, so inject whatever you need: `request_stack`, `security.authorization_checker`, etc.

If you are not using [`JMSDiExtraBundle`](https://github.com/schmittjoh/JMSDiExtraBundle), you can of course
use the traditional way. See [`HomeMenu.php`](Resources/examples/HomeMenu.php) and [`menu.yml`](Resources/examples/menu.yml)

## Integration in template

Simply `knp_menu_render` in your template with the name of your menu.

```twig
{{ knp_menu_render('user') }}
```

## Configuration

Out of the box, the bundle does not require any configuration.

When you create `MenuPartInterface`, a menu will automatically be created with default options.
You can however specify additional options in the general bundle configuration:

```yaml
# app/config/config.yml

wmc_menu_part:
    menus:
        # Applies to all menus, same options as below.
        _all:
            # Useful for Bootstrap menus, etc.
            class: "nav-menu"

        my_menu:
            # Services that will iterates through menu items and possibly hide or modify them.
            visitors:
                - wmc.menu_part.filter.security # ID of a Service implementing MenuVisitorInterface

                ## May also be specified with a priority.
                # the lowest the priority, the earliest the visitor will be run
                # wmc.menu_part.visitor.l10n: { priority: 99 }

            class: "my-awesome-menu"
                ## May also be specified as a list
                # - menu
                # - main-menu

            # Additionnal attributes
            # Use above for classes
            attributes:
                id: "my-menu"
```

## Provided Visitors

### Security filter

This filter (service name: `wmc.menu_part.filter.security`) will hide items the
current user isn't allowed to access.

The current version of this filter relies only on the firewall and doesn't check
the `@Security` annotations. (TODO)

### Localization (L10n) visitor

You first need to enable the visitor for the menus you want to translate.

The following example enable the visitor for all menus:

```yaml
wmc_menu_part:
  menus:
    _all:
      visitors:
        - wmc.menu_part.visitor.l10n
```

This visitor will call the `translator` service on every menu item. The visitor
can be disabled for choosen items by setting the `translation_parameters`
_extra_ to `false`.

To specify a custom translation domain, you can use the `translation_domain`
_extra_.

If `transChoice` is to be used, specify the `translation_number` _extra_.

The item's label will be used as translation key.

Example:

```php
// Assuming the L10n visitor is enabled for the current menu.

// Translated with trans, using no parameters and the default translation domain
$menu->addChild('home', ['route' => 'home']);

// Translated with trans, using the %username% parameter
// and the FOSUserBundle translation domain
$menu->addChild('account', [
                   'route' => 'my_account',
                   'extras' => [
                      'translation_parameters' => ['%username%' => $user],
                      'translation_domain'     => 'FOSUserBundle',
                   ]
               ]);

// Translated with transChoice
$menu->addChild('notifications', [
                   'route' => 'my_notifications',
                   'extras' => [
                      'translation_number' => $user->getNotifications()->count(),
                   ]
               ]);

// Not translated
$menu->addChild(':)', [
                   'route' => 'happy',
                   'extras' => [
                      'translation_parameters' => false,
                   ]
               ]);
```

## Provided Voters

Voters are used to detect the current item(s) in the menu.

See [`voters.yml`](Resources/config/voters.yml) on how to use `RequestVoter` and
`PrefixVoter`.  The file can also be imported as-is in `app/config/config.yml`.

## Author

 * [Sébastien Lavoie](http://blog.lavoie.sl/)
 * [Mathieu Lemoine](http://www.github.com/lemoinem)
 * [WeMakeCustom](http://wemakecustom.com/)
