<?php

namespace SAM\CommonBundle\Annotations;

use SAM\CommonBundle\Annotations\ParamConverter;
use Symfony\Component\HttpFoundation\Request;

class SAMParamConverterManager
{
    private $doctrineParamConverter;

    public function __construct(SAMDoctrineParamConverter $doctrineParamConverter)
    {
        $this->doctrineParamConverter = $doctrineParamConverter;
    }

    /**
     * Applies all converters to the passed configurations and stops when a
     * converter is applied it will move on to the next configuration and so on.
     *
     * @param array|object $configurations
     */
    public function apply(Request $request, $configurations)
    {
        if (is_object($configurations)) {
            $configurations = [$configurations];
        }


        foreach ($configurations as $configuration) {
            $this->applyConverter($request, $configuration);
        }
    }

    /**
     * Applies converter on request based on the given configuration.
     */
    private function applyConverter(Request $request, SAMParamConverter $configuration)
    {
        $value = $request->attributes->get($configuration->getName());
        $className = $configuration->getClassKey();

        // If the value is already an instance of the class we are trying to convert it into
        // we should continue as no conversion is required
        if (is_object($value) && $value instanceof $className) {
            return;
        }

        if (null !== $configuration->getConverter()) {
            if (!$this->doctrineParamConverter->supports($configuration)) {
                throw new \RuntimeException(sprintf(
                    "Converter doctrine does not support conversion of parameter '%s'.",
                    $configuration->getName()
                ));
            }

            $this->doctrineParamConverter->apply($request, $configuration);
        }


        return;
    }
}
