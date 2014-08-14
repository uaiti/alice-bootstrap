<?php
namespace AliceBootstrap\Fixture;

use Doctrine\Common\DataFixtures\Purger\ORMPurger;
use Doctrine\ORM\Tools\SchemaTool;

class Loader
{
	protected $em;

	public function setEM($em)
	{
		$this->em = $em;
	}

	public function createSchema(array $arrEntities)
    {
        $schemaTool = new SchemaTool($this->em);
        $metadataFactory = $this->em->getMetadataFactory();

        $metadata = $this->getEntities($arrEntities);

        foreach ($metadata as $meta) {
            //var_dump($meta);
            // ID com IDENTITY deve vir antes dos que não têm
            if ($meta->generatorType == \Doctrine\ORM\Mapping\ClassMetadata::GENERATOR_TYPE_IDENTITY) {
                // vem antes
            }
            // quem não tem associação/dependência, tem que vir primeiro dos que têm
            if (!$meta->associationMappings) {
                // vem mais antes ainda
            }
        }

        // Drop and recreate tables for all entities
        $schemaTool->dropSchema($metadata);
        $schemaTool->createSchema($metadata);
    }

    public function populate(array $arrEntities) {
        $objects = $this->getDependencyOrdered($arrEntities);

        $persister = new \Nelmio\Alice\ORM\Doctrine($this->em);

        foreach ($objects as $object) {
            $persister->persist([$object]);
        }
    }

    protected function getDependencyOrdered(array $arrEntities)
    {
        $schemaTool = new SchemaTool($this->em);
        $metadataFactory = $this->em->getMetadataFactory();

        $metadata = $this->getEntities($arrEntities);
        
        $alone = $identity = $dependent = array();
        foreach ($metadata as $meta) {
            // first, get the entities without relationship
            if (!$meta->associationMappings) {
                $alone[] = $this->getEntityClassName($meta);
                continue;
            }
            // then get that which have strategy=IDENTITY in the ID
            if ($meta->generatorType == \Doctrine\ORM\Mapping\ClassMetadata::GENERATOR_TYPE_IDENTITY) {
                $identity[] = $this->getEntityClassName($meta);
                continue;
            }

            // and last the ones which has dependencies or N:N without PK
            $dependent[] = $this->getEntityClassName($meta);
        }

        // join in dependency order
        $ordered = array_merge($alone, $identity, $dependent);
        // reorder the objects array so they can be inserted without dependency erros
        $entities = array();
        foreach ($ordered as $className) {
            foreach ($arrEntities as $entity) {
                if (get_class($entity) == $className) {
                    $entities[] = $entity;
                }
            }
        }
        return $entities;
    }

    protected function getEntityClassName(\Doctrine\ORM\Mapping\ClassMetadata $metadata)
    {
        $className = $metadata->rootEntityName;
        if ($metadata->namespace) {
            $className = $metadata->namespace . "\\" . $className;
        }
        return $className;
    }

    protected function getEntities($objects)
    {
        $metadata = array();
        $metadataFactory = $this->em->getMetadataFactory();
        $validClass = array();

        foreach ($objects as $key => $class) {
            $nameClass = get_class($class);
            if (!in_array($nameClass, $validClass)) {
                $metadata[] = $metadataFactory->getMetadataFor($nameClass);
                $validClass[] = $nameClass;
            }
        }

        return $metadata;
    }
}