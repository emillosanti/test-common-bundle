<?php

namespace SAM\CommonBundle\Serializer;

use AppBundle\Entity\Sourcing;
use Symfony\Component\PropertyAccess\PropertyAccessorInterface;
use Symfony\Component\PropertyInfo\PropertyTypeExtractorInterface;
use Symfony\Component\Serializer\Mapping\Factory\ClassMetadataFactoryInterface;
use Symfony\Component\Serializer\NameConverter\NameConverterInterface;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;

class SourcingNormalizer extends ObjectNormalizer
{
    public function __construct(ClassMetadataFactoryInterface $classMetadataFactory = null, NameConverterInterface $nameConverter = null, PropertyAccessorInterface $propertyAccessor = null, PropertyTypeExtractorInterface $propertyTypeExtractor = null)
    {
        parent::__construct($classMetadataFactory, $nameConverter, $propertyAccessor, $propertyTypeExtractor);

        $this->setCircularReferenceHandler(function (Sourcing $object) {
            return $object->getId();
        });
    }

    public function normalize($object, $format = null, array $context = array())
    {
        /** @var $object Sourcing */
        return [
            'id' => $object->getId(),
            'dealFlow' => $object->getDealFlow() ? $object->getDealFlow()->getId() : null,
            'category' => $object->getCategory() ? $object->getCategory()->getId() : null,
        ];
    }

    public function supportsNormalization($data, $format = null)
    {
        return $data instanceof Sourcing;
    }
}