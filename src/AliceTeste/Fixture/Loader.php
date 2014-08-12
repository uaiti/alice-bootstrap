<?php
namespace AliceTeste\Fixture;

use Doctrine\Common\DataFixtures\Purger\ORMPurger;
use Doctrine\ORM\Tools\SchemaTool;

class Loader
{
	protected $em;

	public function setEM($em)
	{
		$this->em = $em;
	}

	public function createSchema(array $arrEntidades)
    {
        $schemaTool = new SchemaTool($this->em);
        $metadataFactory = $this->em->getMetadataFactory();

        $metadata = $this->getEntities($arrEntidades);

        // Drop and recreate tables for all entities
        $schemaTool->dropSchema($metadata);
        $schemaTool->createSchema($metadata);
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