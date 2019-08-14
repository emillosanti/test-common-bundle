<?php

namespace SAM\CommonBundle\Twig;

use SAM\CommonBundle\Entity\BaseContact;

/**
 * Class NumberExtension
 */
class NumberExtension extends \Twig_Extension
{
    /**
     * @return array
     */
    public function getFilters()
    {
        return [
            new \Twig_SimpleFilter('big_number', [$this, 'formatBigNumber']),
            new \Twig_SimpleFilter('format_number', [$this, 'formatNumber']),
        ];
    }

    /**
     * @param int $number
     *
     * @return mixed
     */
    public function formatBigNumber($number)
    {
        if ($number > 1000000) {
            return round($number / 1000000) . 'M';
        }

        return number_format($number, 0, ',', ' ');
    }

    /**
     * Remove trailing zeros
     * @param  decimal $number 
     * @return decimal         
     */
    public function formatNumber($number, $suffix = 'M€', $decimals = null, $nullValue = 'N/A') 
    {
        if (is_numeric($number)) {
            $suffix = ($suffix && !empty($suffix) ? ' ' . $suffix : '');
            
            if (null == $number) {
                return $nullValue;
            } elseif (null === $decimals) {
                $decimals = ( (int) $number != $number ) ? (strlen($number) - strpos($number, '.')) - 1 : 0;
                return number_format($number * 1, $decimals, localeconv()['decimal_point'], ' ') . $suffix;
            } else {
                return rtrim(rtrim(number_format($number, $decimals, localeconv()['decimal_point'], ' '), '0'), localeconv()['decimal_point']) . $suffix;
            }
        } else {
            return $number;
        }
    }
}
