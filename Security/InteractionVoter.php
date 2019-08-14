<?php

namespace SAM\CommonBundle\Security;

use SAM\CommonBundle\Entity\Interaction;
use FOS\UserBundle\Model\UserInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\Authorization\AccessDecisionManagerInterface;

/**
 * Class InteractionVoter.
 */
class InteractionVoter extends Voter
{
    const EDIT = 'CAN_EDIT_INTERACTION';
    const REMOVE = 'CAN_REMOVE_INTERACTION';

    private $decisionManager;

    public function __construct(AccessDecisionManagerInterface $decisionManager)
    {
        $this->decisionManager = $decisionManager;
    }

    /**
     * @return array
     */
    private function getSupportedAttributes()
    {
        return [self::EDIT, self::REMOVE];
    }

    /**
     * @param string $attribute
     * @param object $subject
     *
     * @return bool
     */
    protected function supports($attribute, $subject)
    {
        if (!in_array($attribute, $this->getSupportedAttributes())) {
            return false;
        }

        if (!$subject instanceof Interaction) {
            return false;
        }

        return true;
    }

    /**
     * @param string              $attribute
     * @param Interaction $subject
     * @param TokenInterface      $token
     *
     * @return bool
     */
    protected function voteOnAttribute($attribute, $subject, TokenInterface $token)
    {
        $user = $token->getUser();
        if (!$user instanceof UserInterface) {
            return false;
        }

        switch ($attribute) {
            case self::EDIT:
                return $this->canEdit($subject, $token);
            case self::REMOVE:
                return $this->canRemove($subject, $token);
        }

        return false;
    }

    /**
     * @param Interaction $interaction
     * @param UserInterface       $user
     *
     * @return bool
     */
    private function canEdit(Interaction $interaction, TokenInterface $token)
    {
        if ($this->decisionManager->decide($token, ['ROLE_PROSPECT_ADMIN'])) {
            return true;
        }

        return $interaction->getUser()->getId() === $token->getUser()->getId();
    }

    /**
     * @param Interaction $interaction
     * @param UserInterface       $user
     *
     * @return bool
     */
    private function canRemove(Interaction $interaction, TokenInterface $token)
    {
        return $this->canEdit($interaction, $token);
    }
}
