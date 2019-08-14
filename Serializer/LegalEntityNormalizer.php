<?php

namespace SAM\CommonBundle\Serializer;

use AppBundle\Entity\LegalEntity;
use Symfony\Component\PropertyAccess\PropertyAccessorInterface;
use Symfony\Component\PropertyInfo\PropertyTypeExtractorInterface;
use Symfony\Component\Serializer\Mapping\Factory\ClassMetadataFactoryInterface;
use Symfony\Component\Serializer\NameConverter\NameConverterInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareTrait;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;

class LegalEntityNormalizer extends ObjectNormalizer implements NormalizerInterface, NormalizerAwareInterface
{
    use NormalizerAwareTrait;

    public function __construct(ClassMetadataFactoryInterface $classMetadataFactory = null, NameConverterInterface $nameConverter = null, PropertyAccessorInterface $propertyAccessor = null, PropertyTypeExtractorInterface $propertyTypeExtractor = null)
    {
        parent::__construct($classMetadataFactory, $nameConverter, $propertyAccessor, $propertyTypeExtractor);

        $this->setCircularReferenceHandler(function (LegalEntity $object) {
            return $object->getName();
        });
    }

    public function normalize($object, $format = null, array $context = array())
    {
        /** @var $object LegalEntity */
        return [
            'id' => $object->getId(),
            'name' => $object->getName(),
            'investmentVehicule' => $object->isInvestmentVehicule(),
            'fundsRaised' => (float)$object->getFundsRaised(),
            'parentCompany' => $object->isParentCompany(),
            'parent' => $this->normalizer->normalize($object->getParent(), $format, $context),
            // 'children' => array_map(function (LegalEntity $legalEntity) use ($format, $context) {
            //     return $this->normalizer->normalize($legalEntity, $format, $context);
            // }, $object->getChildren()->toArray()),

        ];
    }

    public function supportsNormalization($data, $format = null)
    {
        return $data instanceof LegalEntity;
    }
}