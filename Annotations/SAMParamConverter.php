<?php

namespace SAM\CommonBundle\Annotations;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\ConfigurationAnnotation;

/**
 * The ParamConverter class handles the ParamConverter annotation parts.
 *
 * @author Fabien Potencier <fabien@symfony.com>
 * @Annotation
 */
class SAMParamConverter extends ConfigurationAnnotation
{
    /**
     * The parameter name.
     *
     * @var string
     */
    private $name;

    /**
     * The parameter class.
     *
     * @var string
     */
    private $classKey;

    /**
     * An array of options.
     *
     * @var array
     */
    private $options = [];

    /**
     * Whether or not the parameter is optional.
     *
     * @var bool
     */
    private $isOptional = false;

    /**
     * Use explicitly named converter instead of iterating by priorities.
     *
     * @var string
     */
    private $converter;

    /**
     * Returns the parameter name.
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Sets the parameter name.
     *
     * @param string $name The parameter name
     */
    public function setValue($name)
    {
        $this->setName($name);
    }

    /**
     * Sets the parameter name.
     *
     * @param string $name The parameter name
     */
    public function setName($name)
    {
        $this->name = $name;
    }

    /**
     * Returns the parameter class key.
     *
     * @return string $name
     */
    public function getClassKey()
    {
        return $this->classKey;
    }

    /**
     * Sets the parameter class key.
     *
     * @param string $classKey The parameter classKey name
     */
    public function setClassKey($classKey)
    {
        $this->classKey = $classKey;
    }

    /**
     * Returns an array of options.
     *
     * @return array
     */
    public function getOptions()
    {
        return $this->options;
    }

    /**
     * Sets an array of options.
     *
     * @param array $options An array of options
     */
    public function setOptions($options)
    {
        $this->options = $options;
    }

    /**
     * Sets whether or not the parameter is optional.
     *
     * @param bool $optional Whether the parameter is optional
     */
    public function setIsOptional($optional)
    {
        $this->isOptional = (bool) $optional;
    }

    /**
     * Returns whether or not the parameter is optional.
     *
     * @return bool
     */
    public function isOptional()
    {
        return $this->isOptional;
    }

    /**
     * Get explicit converter name.
     *
     * @return string
     */
    public function getConverter()
    {
        return $this->converter;
    }

    /**
     * Set explicit converter name.
     *
     * @param string $converter
     */
    public function setConverter($converter)
    {
        $this->converter = $converter;
    }

    /**
     * Returns the annotation alias name.
     *
     * @return string
     *
     * @see ConfigurationInterface
     */
    public function getAliasName()
    {
        return 'converters';
    }

    /**
     * Multiple ParamConverters are allowed.
     *
     * @return bool
     *
     * @see ConfigurationInterface
     */
    public function allowArray()
    {
        return true;
    }
}
