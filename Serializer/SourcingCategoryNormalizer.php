<?php

namespace SAM\CommonBundle\Serializer;

use SAM\CommonBundle\Entity\SourcingCategory;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;

class SourcingCategoryNormalizer extends ObjectNormalizer
{
    public function normalize($object, $format = null, array $context = array())
    {
        /** @var $object SourcingCategory */
        return [
            'id' => $object->getId(),
            'name' => $object->getName(),
        ];
    }

    public function supportsNormalization($data, $format = null)
    {
        return $data instanceof SourcingCategory;
    }
}