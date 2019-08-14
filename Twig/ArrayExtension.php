<?php

namespace SAM\CommonBundle\Twig;

use Doctrine\Common\Collections\ArrayCollection;
use SAM\CommonBundle\Entity\BaseContact;

class ArrayExtension extends \Twig_Extension
{
    public function getFilters()
    {
        return [
            new \Twig_SimpleFilter('arrayFilter', [$this, 'arrayFilter']),
            new \Twig_Filter('arrayMapObjectProperty', [$this, 'arrayMapObjectProperty']),
        ];
    }

    /**
     * Remove entry in array by keys
     *
     * @param  array  $array
     * @param  array  $keysToRemove Keys to remove
     * @return array               filtered array
     */
    public function arrayFilter(array $array, $keysToRemove = [])
    {
        return array_filter($array,
            function ($key) use ($keysToRemove) {
                return !in_array($key, $keysToRemove);
            },
            ARRAY_FILTER_USE_KEY);
    }

    /**
     * @param array|ArrayCollection $array
     * @param string $column
     * @return array
     */
    public function arrayMapObjectProperty($array, $column)
    {
        if ($array instanceof ArrayCollection) {
            $array = $array->getValues();
        }

        return array_map(
            function ($item) use ($column) {
                return $item->{'get' . ucfirst($column)}();
            },
            $array
        );
    }
}
