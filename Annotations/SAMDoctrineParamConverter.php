<?php

namespace SAM\CommonBundle\Annotations;

use SAM\CommonBundle\Annotations\SAMParamConverter;
use Symfony\Component\ExpressionLanguage\ExpressionLanguage;
use Symfony\Component\ExpressionLanguage\SyntaxError;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\Common\Persistence\ManagerRegistry;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\NoResultException;

/**
 * SAMDoctrineParamConverter
 */
class SAMDoctrineParamConverter
{
    /**
     * @var array
     */
    private $entities;

    /**
     * @var ManagerRegistry
     */
    private $registry;

    public function __construct($entities, ManagerRegistry $registry = null)
    {
        $this->entities = $entities;
        $this->registry = $registry;
    }

    /**
     * {@inheritdoc}
     *
     * @throws \LogicException       When unable to guess how to get a Doctrine instance from the request information
     * @throws NotFoundHttpException When object not found
     */
    public function apply(Request $request, SAMParamConverter $configuration)
    {
        $name = $configuration->getName();
        $classKey = $configuration->getClassKey();
        if (!array_key_exists($classKey, $this->entities)) {
            throw new \LogicException(sprintf('Unable to find the class name from the key: "%s".', $classKey));
        }
        $class = $this->entities[$classKey];
        $options = $this->getOptions($configuration);

        if (null === $request->attributes->get($name, false)) {
            $configuration->setIsOptional(true);
        }

        $errorMessage = null;
        if ($this->find($classKey, $class, $request, $options, $name)) {
            // find by criteria
            if (false === $object = $this->findOneBy($classKey, $class, $request, $options)) {
                if ($configuration->isOptional()) {
                    $object = null;
                } else {
                    throw new \LogicException(sprintf('Unable to guess how to get a Doctrine instance from the request information for parameter "%s".', $name));
                }
            }
        }

        if (null === $object && false === $configuration->isOptional()) {
            $message = sprintf('%s object not found by the @%s annotation.', $classKey, $this->getAnnotationName($configuration));
            if ($errorMessage) {
                $message .= ' '.$errorMessage;
            }
            throw new NotFoundHttpException($message);
        }

        $request->attributes->set($name, $object);

        return true;
    }

    private function find($classKey, $class, Request $request, $options, $name)
    {
        if ($options['mapping'] || $options['exclude']) {
            return false;
        }

        $id = $this->getIdentifier($request, $options, $name);

        if (false === $id || null === $id) {
            return false;
        }

        if ($options['repository_method']) {
            $method = $options['repository_method'];
        } else {
            $method = 'find';
        }

        $om = $this->getManager($options['entity_manager'], $class);
        if ($options['evict_cache'] && $om instanceof EntityManagerInterface) {
            $cacheProvider = $om->getCache();
            if ($cacheProvider && $cacheProvider->containsEntity($class, $id)) {
                $cacheProvider->evictEntity($class, $id);
            }
        }

        try {
            return $om->getRepository($classKey)->$method($id);
        } catch (NoResultException $e) {
            return;
        }
    }

    private function getIdentifier(Request $request, $options, $name)
    {
        if (null !== $options['id']) {
            if (!is_array($options['id'])) {
                $name = $options['id'];
            } elseif (is_array($options['id'])) {
                $id = [];
                foreach ($options['id'] as $field) {
                    if (false !== strstr($field, '%s')) {
                        // Convert "%s_uuid" to "foobar_uuid"
                        $field = sprintf($field, $name);
                    }
                    $id[$field] = $request->attributes->get($field);
                }

                return $id;
            }
        }

        if ($request->attributes->has($name)) {
            return $request->attributes->get($name);
        }

        if ($request->attributes->has('id') && !$options['id']) {
            return $request->attributes->get('id');
        }

        return false;
    }

    private function findOneBy($classKey, $class, Request $request, $options)
    {
        if (!$options['mapping']) {
            $keys = $request->attributes->keys();
            $options['mapping'] = $keys ? array_combine($keys, $keys) : [];
        }

        foreach ($options['exclude'] as $exclude) {
            unset($options['mapping'][$exclude]);
        }

        if (!$options['mapping']) {
            return false;
        }

        // if a specific id has been defined in the options and there is no corresponding attribute
        // return false in order to avoid a fallback to the id which might be of another object
        if ($options['id'] && null === $request->attributes->get($options['id'])) {
            return false;
        }

        $criteria = [];
        $em = $this->getManager($options['entity_manager'], $class);
        $metadata = $em->getClassMetadata($class);

        $mapMethodSignature = $options['repository_method']
            && $options['map_method_signature']
            && true === $options['map_method_signature'];

        foreach ($options['mapping'] as $attribute => $field) {
            if ($metadata->hasField($field)
                || ($metadata->hasAssociation($field) && $metadata->isSingleValuedAssociation($field))
                || $mapMethodSignature) {
                $criteria[$field] = $request->attributes->get($attribute);
            }
        }

        if ($options['strip_null']) {
            $criteria = array_filter($criteria, function ($value) {
                return null !== $value;
            });
        }

        if (!$criteria) {
            return false;
        }

        if ($options['repository_method']) {
            $repositoryMethod = $options['repository_method'];
        } else {
            $repositoryMethod = 'findOneBy';
        }

        try {
            if ($mapMethodSignature) {
                return $this->findDataByMapMethodSignature($em, $classKey, $repositoryMethod, $criteria);
            }

            return $em->getRepository($classKey)->$repositoryMethod($criteria);
        } catch (NoResultException $e) {
            return;
        }
    }

    private function findDataByMapMethodSignature($em, $classKey, $repositoryMethod, $criteria)
    {
        $arguments = [];
        $repository = $em->getRepository($classKey);
        $ref = new \ReflectionMethod($repository, $repositoryMethod);
        foreach ($ref->getParameters() as $parameter) {
            if (array_key_exists($parameter->name, $criteria)) {
                $arguments[] = $criteria[$parameter->name];
            } elseif ($parameter->isDefaultValueAvailable()) {
                $arguments[] = $parameter->getDefaultValue();
            } else {
                throw new \InvalidArgumentException(sprintf('Repository method "%s::%s" requires that you provide a value for the "$%s" argument.', get_class($repository), $repositoryMethod, $parameter->name));
            }
        }

        return $ref->invokeArgs($repository, $arguments);
    }

    /**
     * {@inheritdoc}
     */
    public function supports(SAMParamConverter $configuration)
    {
        // if there is no manager, this means that only Doctrine DBAL is configured
        if (null === $this->registry || !count($this->registry->getManagerNames())) {
            return false;
        }

        if (null === $configuration->getClassKey()) {
            return false;
        }

        $options = $this->getOptions($configuration, false);
        if (!array_key_exists($configuration->getClassKey(), $this->entities)) {
            return false;
        }

        // Doctrine Entity?
        $em = $this->getManager($options['entity_manager'], $class);
        if (null === $em) {
            return false;
        }

        return !$em->getMetadataFactory()->isTransient($class);
    }

    private function getOptions(SAMParamConverter $configuration, $strict = true)
    {
        $defaultValues = [
            'entity_manager' => null,
            'exclude' => [],
            'mapping' => [],
            'strip_null' => false,
            'expr' => null,
            'id' => null,
            'repository_method' => null,
            'map_method_signature' => false,
            'evict_cache' => false,
        ];

        $passedOptions = $configuration->getOptions();

        if (isset($passedOptions['repository_method'])) {
            @trigger_error('The repository_method option of @SAMParamConverter is deprecated and will be removed in 6.0. Use the expr option or @Entity.', E_USER_DEPRECATED);
        }

        if (isset($passedOptions['map_method_signature'])) {
            @trigger_error('The map_method_signature option of @SAMParamConverter is deprecated and will be removed in 6.0. Use the expr option or @Entity.', E_USER_DEPRECATED);
        }

        $extraKeys = array_diff(array_keys($passedOptions), array_keys($defaultValues));
        if ($extraKeys && $strict) {
            throw new \InvalidArgumentException(sprintf('Invalid option(s) passed to @%s: %s', $this->getAnnotationName($configuration), implode(', ', $extraKeys)));
        }

        return array_replace($defaultValues, $passedOptions);
    }

    private function getManager($name, $classKey)
    {
        if (null === $name) {
            return $this->registry->getManagerForClass($classKey);
        }

        return $this->registry->getManager($name);
    }

    private function getAnnotationName(SAMParamConverter $configuration)
    {
        $r = new \ReflectionClass($configuration);

        return $r->getShortName();
    }
}
