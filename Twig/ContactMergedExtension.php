<?php

namespace SAM\CommonBundle\Twig;

use SAM\AddressBookBundle\Entity\ContactMergedReminder;

/**
 * Class ContactMergedExtension
 */
class ContactMergedExtension extends \Twig_Extension
{
    /**
     * @return array
     */
    public function getFilters()
    {
        return [
            new \Twig_SimpleFilter('means_of_contact_icon', [$this, 'getMeansOfContactIcon']),
        ];
    }

    /**
     * @return string
     */
    public function getMeansOfContactIcon($value)
    {
        $icon = 'question';

        switch ($value) {
            case ContactMergedReminder::MEANS_OF_CONTACT_PHONE:
                $icon = 'phone';
                break;
            case ContactMergedReminder::MEANS_OF_CONTACT_MAIL:
                $icon = 'at';
                break;
            case ContactMergedReminder::MEANS_OF_CONTACT_POSTAL_MAIL:
                $icon = 'inbox';
                break;
            case ContactMergedReminder::MEANS_OF_CONTACT_MEETING:
                $icon = 'calendar-check-o';
                break;
        }

        return $icon;
    }
}
