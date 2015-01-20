<?php

namespace WMC\MenuPartBundle\Visitor;

use WMC\MenuPartBundle\Visitors\AbstractMenuVisitor;

use Symfony\Component\Security\Http\Firewall\AccessListener;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Security\Core\Exception\AuthenticationCredentialsNotFoundException;

use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpFoundation\RequestStack;

use Knp\Menu\MenuItem;

/**
 * Calls Symfony\Component\Security\Http\Firewall\AccessListener
 * to see if current user is allowed to see an item.
 *
 * Does not check @Security annotations, only the firewall rules.
 */
class SecurityFilterVisitor extends AbstractMenuVisitor
{
    /**
     * @var Symfony\Component\HttpKernel\HttpKernelInterface
     */
    protected $kernel;

    /**
     * @var Symfony\Component\Security\Http\Firewall\AccessListener
     */
    protected $accessListener;

    /**
     * @var Symfony\Component\HttpFoundation\RequestStack
     */
    protected $requestStack;

    public function __construct(HttpKernelInterface $kernel, AccessListener $accessListener, RequestStack $requestStack)
    {
        $this->kernel = $kernel;
        $this->accessListener = $accessListener;
        $this->requestStack = $requestStack;
    }

    protected function getClonedRequest($new_uri)
    {
        $request = $this->requestStack->getCurrentRequest()->duplicate();

        $request->server->set('REQUEST_URI', $new_uri);

        return $request;
    }

    public function setContainer(ContainerInterface $container = null)
    {
        $this->container = $container;
    }

    public function visitMenuItem(MenuItem $item)
    {
        try {
            $request = $this->getClonedRequest($item->getUri());

            $event = new GetResponseEvent($this->kernel, $request, HttpKernelInterface::MASTER_REQUEST);

            $this->accessListener->handle($event);
        } catch (AccessDeniedException $e) {
            $item->setDisplay(false);
        } catch (AuthenticationCredentialsNotFoundException $e) {
        }
    }
}
