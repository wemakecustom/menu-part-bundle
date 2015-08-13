<?php

namespace WMC\MenuPartBundle\Visitor;

use WMC\MenuPartBundle\Visitors\AbstractMenuVisitor;

use Symfony\Component\Security\Http\Firewall\AccessListener;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Security\Core\Exception\AuthenticationCredentialsNotFoundException;

use Symfony\Component\Translation\TranslatorInterface;

use Knp\Menu\MenuItem;

/**
 * Calls translator service to translate item label.
 *
 * Uses the translator only if either the extra translation_parameters or
 * translation_domain is set (an empty array for the parameters is fine).
 */
class L10nVisitor extends AbstractMenuVisitor
{
    /**
     * @var Symfony\Component\Translation\TranslatorInterface
     */
    protected $translator;

    public function __construct(TranslatorInterface $translator)
    {
        $this->translator = $translator;
    }

    public function visitMenuItem(MenuItem $item)
    {
       $domain     = $item->getExtra('translation_domain');
       $parameters = $item->getExtra('translation_parameters');

       if (false === $parameters) {
          return;
       } elseif (!is_array($parameters)) {
          $parameters = [];
       }

       $id = $item->getLabel();

       if (null !== ($number = $item->getExtra('translation_number'))) {
          $item->setLabel($this->translator->transChoice($id, $number, $parameters, $domain));
       } else {
          $item->setLabel($this->translator->trans($id, $parameters, $domain));
       }
    }
}
