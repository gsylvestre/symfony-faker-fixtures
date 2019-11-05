<?php

namespace FakerFixtures\Doctrine;

use Doctrine\Common\Inflector\Inflector;
use Doctrine\ORM\Mapping\ClassMetadata;

final class AssociationFieldData extends FieldData
{
    protected $isAssoc = true;

    private $associatedShortClassName;
    private $associatedFullClassName;
    private $associatedShortPluralClassName;
    private $pivotTableName;

    private $isOwningSide = false;

    private $joinColumns;

    public function __construct(string $fieldName, ClassMetadata $classMetaData)
    {
        parent::__construct($fieldName, $classMetaData);

        $association = $classMetaData->getAssociationMapping($fieldName);

        $this->setType($association['type']);

        $this->setIsOwningSide($association['isOwningSide']);

        if (DependencyGraph::isADependantAssociation($association)){

            $targetEntityReflectionClass = new \ReflectionClass($association['targetEntity']);
            $this->setAssociatedShortClassName($targetEntityReflectionClass->getShortName());
            $this->setAssociatedFullClassName($targetEntityReflectionClass->getName());
            $this->setAssociatedShortPluralClassName(Inflector::pluralize($targetEntityReflectionClass->getShortName()));
            $this->setPivotTableName(!empty($association['joinTable']['name']) ? $association['joinTable']['name'] : "");

            if ($this->getType() === DependencyGraph::ONETOONE || $this->getType() === DependencyGraph::MANYTOONE){
                $this->setSetter($this->guessSetterName());
            }
            else {
                $this->setAdder($this->guessAdderName());
            }
        }
    }

    /**
     * @return mixed
     */
    public function getisAssoc()
    {
        return $this->isAssoc;
    }

    /**
     * @return mixed
     */
    public function getAssociatedShortClassName()
    {
        return $this->associatedShortClassName;
    }

    /**
     * @param mixed $associatedShortClassName
     */
    public function setAssociatedShortClassName($associatedShortClassName): void
    {
        $this->associatedShortClassName = $associatedShortClassName;
    }

    /**
     * @return mixed
     */
    public function getAssociatedFullClassName()
    {
        return $this->associatedFullClassName;
    }

    /**
     * @param mixed $associatedFullClassName
     */
    public function setAssociatedFullClassName($associatedFullClassName): void
    {
        $this->associatedFullClassName = $associatedFullClassName;
    }

    /**
     * @return mixed
     */
    public function getAssociatedShortPluralClassName()
    {
        return $this->associatedShortPluralClassName;
    }

    /**
     * @param mixed $associatedShortPluralClassName
     */
    public function setAssociatedShortPluralClassName($associatedShortPluralClassName): void
    {
        $this->associatedShortPluralClassName = $associatedShortPluralClassName;
    }

    /**
     * @return mixed
     */
    public function getPivotTableName()
    {
        return $this->pivotTableName;
    }

    /**
     * @param mixed $pivotTableName
     */
    public function setPivotTableName($pivotTableName): void
    {
        $this->pivotTableName = $pivotTableName;
    }

    /**
     * @return mixed
     */
    public function getJoinColumns()
    {
        return $this->joinColumns;
    }

    /**
     * @param mixed $joinColumns
     */
    public function setJoinColumns($joinColumns): void
    {
        $this->joinColumns = $joinColumns;
    }

    /**
     * @return mixed
     */
    public function getisOwningSide()
    {
        return $this->isOwningSide;
    }

    /**
     * @param mixed $isOwningSide
     */
    public function setIsOwningSide($isOwningSide): void
    {
        $this->isOwningSide = $isOwningSide;
    }


}