<?php

namespace FakerFixtures\Doctrine;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Inflector\Inflector;
use Doctrine\ORM\Mapping\ClassMetadata;
use FakerFixtures\Security\UserClassHelper;

class ClassData
{
    private $shortClassName;
    private $fullClassName;
    private $shortPluralClassName;
    private $tableName;
    private $pivotTableNames;
    private $securityUserClass;

    private $fields;

    public function __construct(ClassMetadata $classMetaData)
    {
        $this->fields = new ArrayCollection();

        $reflectionClass = new \ReflectionClass($classMetaData->getName());

        $this->setFullClassName($reflectionClass->getName());
        $this->setShortClassName($reflectionClass->getShortName());
        $this->setShortPluralClassName(Inflector::pluralize($reflectionClass->getShortName()));
        $this->setTableName($classMetaData->getTableName());

        $pivotTableNames = AssociationHelper::getPivotTableNames($classMetaData);
        $this->setPivotTableNames($pivotTableNames);

    }

    /**
     * @return mixed
     */
    public function getShortClassName()
    {
        return $this->shortClassName;
    }

    /**
     * @param mixed $shortClassName
     */
    public function setShortClassName($shortClassName): void
    {
        $this->shortClassName = $shortClassName;
    }

    /**
     * @return mixed
     */
    public function getFullClassName()
    {
        return $this->fullClassName;
    }

    /**
     * @param mixed $fullClassName
     */
    public function setFullClassName($fullClassName): void
    {
        $this->fullClassName = $fullClassName;
    }

    /**
     * @return mixed
     */
    public function getShortPluralClassName()
    {
        return $this->shortPluralClassName;
    }

    /**
     * @param mixed $shortPluralClassName
     */
    public function setShortPluralClassName($shortPluralClassName): void
    {
        $this->shortPluralClassName = $shortPluralClassName;
    }

    /**
     * @return mixed
     */
    public function getTableName()
    {
        return $this->tableName;
    }

    /**
     * @param mixed $tableName
     */
    public function setTableName($tableName): void
    {
        $this->tableName = $tableName;
    }

    /**
     * @return mixed
     */
    public function getPivotTableNames()
    {
        return $this->pivotTableNames;
    }

    /**
     * @param mixed $pivotTableNames
     */
    public function setPivotTableNames($pivotTableNames): void
    {
        $this->pivotTableNames = $pivotTableNames;
    }

    /**
     * @return mixed
     */
    public function getSecurityUserClass()
    {
        return $this->securityUserClass;
    }

    /**
     * @param mixed $securityUserClass
     */
    public function setSecurityUserClass($securityUserClass): void
    {
        $this->securityUserClass = $securityUserClass;
    }

    /**
     * @return ArrayCollection
     */
    public function getFields(): ArrayCollection
    {
        return $this->fields;
    }

    /**
     * @param FieldData $fieldData
     */
    public function addField(FieldData $fieldData): void
    {
        $this->fields->add($fieldData);
    }
}