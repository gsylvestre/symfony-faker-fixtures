<?php

namespace FakerFixtures\Doctrine;

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

    /**
     * Help sort the entities
     * @var array
     */
    private $waitingRoom = [];

    /**
     * Help avoiding infinite loop
     * @var null
     */
    private $lastNumberInWaitingRoom = null;

    /**
     * DependencyGraph constructor.
     * @param array $classMetas
     */
    public function __construct(array $classMetas)
    {
        $this->classMetas = $classMetas;
        $this->buildDepencyGraph();
    }

    /**
     * Determine the sort order
     *
     * @throws \Exception
     */
    private function buildDepencyGraph():void
    {
        /** @var ClassMetadata $meta */
        foreach($this->classMetas as $meta){
            //entity has no depency, we add it right away at the beginning
            if (!$this->hasDependencyRelations($meta)){
                $this->order[] = $meta->getName();
            }
            else {
                //this class will have to wait
                $this->waitingRoom[] = $meta;
            }
        }

        //start handle entities in waiting room
        $this->handleWaitingRoom();
    }

    /**
     * Recursive function that sort classes based on relationship dependencies
     *
     * @throws \Exception
     */
    private function handleWaitingRoom(): void
    {
        //avoid infinite loops
        $this->lastNumberInWaitingRoom = count($this->waitingRoom);

        for($i=0; $i<count($this->waitingRoom); $i++){
            //all deps already in $order ?
            if ($this->dependenciesAreAllSatisfied($this->waitingRoom[$i])){
                //remove the entity from waiting room and add it to $order
                $this->order[] = $this->waitingRoom[$i]->getName();
                unset($this->waitingRoom[$i]);
                $this->waitingRoom = array_values($this->waitingRoom);
                break;
            }
        }

        //avoir infinite llops
        if (count($this->waitingRoom) === $this->lastNumberInWaitingRoom){
            throw new \Exception("something wrong with relations. Infinite loop.");
        }

        //not done, go again
        if (!empty($this->waitingRoom)){
            $this->handleWaitingRoom();
        }
    }

    /**
     * Test if all relation dependencies are already in $order
     *
     * @param ClassMetadata $meta
     * @return bool
     */
    private function dependenciesAreAllSatisfied(ClassMetadata $meta): bool
    {
        foreach($meta->getAssociationMappings() as $property => $infos){
            //many to one or many to many owning side
            if ($infos['type'] === self::MANYTOONE ||
                ($infos['type'] === self::MANYTOMANY && $infos['isOwningSide']))
            {
                //dependency missing, not ready to get out of waiting room
                if (!in_array($infos['targetEntity'], $this->order)){
                    return false;
                }
            }
        }

        return true;
    }

    /**
     * Determines if an entity has dependecy relationships
     *
     * @param ClassMetadata $meta
     * @return bool
     */
    private function hasDependencyRelations(ClassMetadata $meta): bool
    {
        $associationMappings = $meta->getAssociationMappings();
        if (empty($associationMappings)){
            return false;
        }

        foreach($associationMappings as $property => $infos){
            if (self::isADependantAssociation($infos)){
                return true;
            }
        }

        return false;
    }

    public static function isADependantAssociation($associationMapping)
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

    /**
     * @return array
     */
    public function getOrder(): array
    {
        return $this->order;
    }
}