<?php

namespace WMC\MenuBundle\Visitor;

use WMC\MenuBundle\Visitors\AbstractMenuVisitor;

use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

use Symfony\Component\Security\Http\Firewall\AccessListener;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Security\Core\Exception\AuthenticationCredentialsNotFoundException;

use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;

use Knp\Menu\MenuItem;

class SecurityFilterVisitor extends AbstractMenuVisitor implements ContainerAwareInterface
{
    protected $access_listener;

    protected $kernel;

    protected $container;

    public function __construct(HttpKernelInterface $kernel, AccessListener $access_listener)
    {
        parent::__construct();
        $this->kernel = $kernel;
        $this->access_listener = $access_listener;
    }

    protected function getClonedRequest($new_uri)
    {
        $cloned_request = $this->container->get('request')->duplicate();

        $cloned_request->server->set('REQUEST_URI', $new_uri);

        return $cloned_request;
    }

    public function setContainer(ContainerInterface $container = null)
    {
        $this->container = $container;
    }

    public function visitMenuItem(MenuItem $item)
    {
        try {
            $item_request = $this->getClonedRequest($item->getUri());

            $item_request_event = new GetResponseEvent($this->kernel, $item_request, HttpKernelInterface::MASTER_REQUEST);

            $this->access_listener->handle($item_request_event);
        } catch (AccessDeniedException $e) {
            $item->setDisplay(false);
        } catch (AuthenticationCredentialsNotFoundException $e) {
        }
    }
}
