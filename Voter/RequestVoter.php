<?php

namespace WMC\MenuPartBundle\Voter;

use Knp\Menu\ItemInterface;
use Knp\Menu\Matcher\Voter\VoterInterface;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * Voter based on the uri
 */
class RequestVoter implements VoterInterface
{
    /**
     * @var Symfony\Component\HttpFoundation\RequestStack
     */
    protected $requestStack;

    public function __construct(RequestStack $requestStack)
    {
        $this->requestStack = $requestStack;
    }

    /**
     * {@inheritDoc}
     */
    public function matchItem(ItemInterface $item)
    {
        if ($item->getUri() === $this->getRequestUri()) {
            return true;
        }

        return null;
    }

    protected function getRequestUri()
    {
        return $this->requestStack->getCurrentRequest()->getRequestUri();
    }
}
