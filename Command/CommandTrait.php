<?php

namespace SAM\CommonBundle\Command;

trait CommandTrait
{
    protected function info($text)
    {
        return $this->formatSection($this->getDatetime(), $text, 'info');
    }

    protected function comment($text)
    {
        return $this->formatSection($this->getDatetime(), $text, 'comment');
    }

    protected function error($text, \Exception $exception = null)
    {
        $array = $exception ? [$text, $exception->getMessage()] : [ $text ];
        return $this->formatBlock($array);
    }

    protected function formatSection($title, $text, $textLevel = 'info')
    {
        return $this->getHelper('formatter')->formatSection(
            $title,
            '<'.$textLevel.'>'.$text.'</'.$textLevel.'>'
        );
    }

    protected function formatBlock($errorMessages)
    {
        return $this->getHelper('formatter')->formatBlock($errorMessages, 'error', true);
    }

    protected function getDatetime()
    {
        return (new \DateTime())->format('Y-m-d H:i:s');
    }
}
