<?php

namespace FakerFixtures\Doctrine;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Internal\CommitOrderCalculator;
use Doctrine\ORM\Mapping\ClassMetadata;

/**  @TODO : handle all relation types ! */

/**
 *
 * Determine order in which to load the fixtures.
 *
 * Class DependencyGraph
 * @package FakerFixtures\Doctrine
 */
class DependencyGraph
{
    //doctrine relation types
    const ONETOMANY = 4;
    const MANYTOONE = 2;
    const MANYTOMANY = 8;
    const ONETOONE = 1;

    /**
     * @var array
     */
    private $classMetas = [];

    /**
     * Will contains the classes data in order
     * @var array
     */
    private $order = [];

    private $dependencyGraph;

    /** @var EntityManager */
    private $em;

    /**
     * DependencyGraph constructor.
     * @param array $classMetas
     */
    public function __construct(array $classMetas, $em)
    {
        $this->classMetas = $classMetas;
        $this->em = $em;
        $this->dependencyGraph = $this->buildDepencyGraph();
        foreach($this->dependencyGraph as $classMeta){
            $this->order[] = $classMeta->getName();
        }
    }

    /**
     * Determine the sort order
     *
     * @throws \Exception
     */
    private function buildDepencyGraph():array
    {
        $calc = new CommitOrderCalculator();

        $newNodes = [];

        foreach ($this->classMetas as $class) {
            $calc->addNode($class->name, $class);
            $newNodes[] = $class;
        }

        // Calculate dependencies for new nodes
        while ($class = array_pop($newNodes)) {
            foreach ($class->associationMappings as $assoc) {
                if ( ! ($assoc['isOwningSide'] && $assoc['type'] & ClassMetadata::TO_ONE)) {
                    continue;
                }

                $targetClass = $this->em->getClassMetadata($assoc['targetEntity']);

                if ( ! $calc->hasNode($targetClass->name)) {
                    $calc->addNode($targetClass->name, $targetClass);

                    $newNodes[] = $targetClass;
                }

                $joinColumns = reset($assoc['joinColumns']);

                $calc->addDependency($targetClass->name, $class->name, (int)empty($joinColumns['nullable']));

                // If the target class has mapped subclasses, these share the same dependency.
                if ( ! $targetClass->subClasses) {
                    continue;
                }

                foreach ($targetClass->subClasses as $subClassName) {
                    $targetSubClass = $this->em->getClassMetadata($subClassName);

                    if ( ! $calc->hasNode($subClassName)) {
                        $calc->addNode($targetSubClass->name, $targetSubClass);

                        $newNodes[] = $targetSubClass;
                    }

                    $calc->addDependency($targetSubClass->name, $class->name, 1);
                }
            }
        }

        return $calc->sort();
    }

    /**
     * @return array
     */
    public function getOrder(): array
    {
        return $this->order;
    }

    /**
     * @return array
     */
    public function getDependencyGraph(): array
    {
        return $this->dependencyGraph;
    }


    public static function isADependantAssociation($associationMapping): bool
    {
        if ($associationMapping['type'] === self::MANYTOONE){
            return true;
        }
        if (in_array($associationMapping['type'], [self::MANYTOMANY, self::ONETOONE])){
            if ($associationMapping['isOwningSide']){
                return true;
            }
        }
        return false;
    }
}