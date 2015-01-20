<?php

namespace WMC\MenuPartBundle\Voter;

use Knp\Menu\ItemInterface;
use Knp\Menu\Matcher\Voter\VoterInterface;

/**
 * Voter based on the uri
 */
class PrefixVoter extends RequestVoter
{
    /**
     * @var boolean
     */
    private $defaultActive = false;

    public function setDefaultActive($defaultActive)
    {
        $this->defaultActive = $defaultActive;
    }

    /**
     * {@inheritDoc}
     */
    public function matchItem(ItemInterface $item)
    {
        if ($prefix = $item->getExtra('prefix_match', $this->defaultActive)) {
            if (strpos($this->getRequestUri(), $item->getUri()) === 0) {
                return true;
            }
        }

        return null;
    }
}
