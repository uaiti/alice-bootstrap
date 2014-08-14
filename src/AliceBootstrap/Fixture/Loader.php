<?php
namespace AliceBootstrap\Fixture;

use Doctrine\Common\DataFixtures\Purger\ORMPurger;
use Doctrine\ORM\Tools\SchemaTool;

class Loader
{
    const ENTITY_TYPE_DEPENDENT = 'dependent';
    const ENTITY_TYPE_IDENTITY  = 'identity';
    const ENTITY_TYPE_ALONE     = 'alone';

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
        $ordered = $this->getDependencyOrdered($arrEntities);

        $persister = new \Nelmio\Alice\ORM\Doctrine($this->em);

        $persister->persist($ordered[self::ENTITY_TYPE_ALONE]);
        $persister->persist($ordered[self::ENTITY_TYPE_IDENTITY]);
        $persister->persist($ordered[self::ENTITY_TYPE_DEPENDENT]);
    }

    public function clear(array $arrEntities)
    {
        $ordered = $this->getDependencyOrdered($arrEntities);
        $this->truncate($ordered);
    }


    protected function truncate(array $arrEntities = null, $flush = true)
    {
        // disable foreign key checks so there won't be dependency errors
        $sql = "SET FOREIGN_KEY_CHECKS=0";
        $this->em->getConnection()->executeQuery($sql);
        foreach ($arrEntities as $object) {
            if (is_array($object)) {
                $this->truncate($object, false);
                continue;
            }
            $this->em->remove($object);
        }

        if ($flush) {
            $this->em->flush();
        }
    }

    protected function getDependencyOrdered(array $arrEntities)
    {
        $schemaTool = new SchemaTool($this->em);
        $metadataFactory = $this->em->getMetadataFactory();

        $metadata = $this->getEntities($arrEntities);
        
        $ordered = array(
            self::ENTITY_TYPE_ALONE => array(),
            self::ENTITY_TYPE_IDENTITY => array(),
            self::ENTITY_TYPE_DEPENDENT => array()
        );
        foreach ($metadata as $meta) {
            // first, get the entities without relationship
            if (!$meta->associationMappings) {
                $ordered[self::ENTITY_TYPE_ALONE][] = $this->getEntityClassName($meta);
                continue;
            }
            // then get that which have strategy=IDENTITY in the ID
            if ($meta->generatorType == \Doctrine\ORM\Mapping\ClassMetadata::GENERATOR_TYPE_IDENTITY) {
                $ordered[self::ENTITY_TYPE_IDENTITY][] = $this->getEntityClassName($meta);
                continue;
            }

            // and last the ones which has dependencies or N:N without PK
            $ordered[self::ENTITY_TYPE_DEPENDENT][] = $this->getEntityClassName($meta);
        }

        // reorder the objects array so they can be inserted without dependency erros
        $entities = $this->classify($ordered, $arrEntities);
        return $entities;
    }

    protected function classify($ordered, $arrEntities)
    {
        $classified = array();
        foreach ($ordered as $type => $itens) {
            $classified[$type] = array();
            foreach ($itens as $className) {
                $classified[$type] = array_merge($classified[$type], $this->getObjectEntities($arrEntities, $className));
            }
        }
        return $classified;
    }

    protected function getObjectEntities($arrEntities, $className)
    {
        $entities = array();
        foreach ($arrEntities as $entity) {
            if (get_class($entity) == $className) {
                $entities[] = $entity;
            }
        }
        return $entities;
    }

    protected function getEntityClassName(\Doctrine\ORM\Mapping\ClassMetadata $metadata)
    {
        $className = $metadata->rootEntityName;
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