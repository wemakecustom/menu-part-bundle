<?php
/**
 * @link https://github.com/KnpLabs/KnpMenuBundle/issues/122
 */

namespace WMC\MenuBundle\Voter;

use Knp\Menu\ItemInterface;
use Knp\Menu\Matcher\Voter\VoterInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Voter based on the uri
 */
class PrefixVoter implements VoterInterface
{
    /**
     * @var boolean
     */
    private $default_active = false;

    /**
     * @var \Symfony\Component\DependencyInjection\ContainerInterface
     */
    private $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    public function setDefaultActive($default_active)
    {
        $this->default_active = $default_active;
    }

    /**
     * Checks whether an item is current.
     *
     * If the voter is not able to determine a result,
     * it should return null to let other voters do the job.
     *
     * @param  ItemInterface $item
     * @return boolean|null
     */
    public function matchItem(ItemInterface $item)
    {
        if ($prefix = $item->getExtra('prefix_match', $this->default_active)) {
            if (strpos($this->container->get('request')->getRequestUri(), $item->getUri()) === 0) {
                return true;
            }
        }

        return null;
    }
}
