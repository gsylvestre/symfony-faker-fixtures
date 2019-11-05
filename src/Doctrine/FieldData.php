<?php

namespace FakerFixtures\Doctrine;

use Doctrine\Common\Inflector\Inflector;
use Doctrine\ORM\Mapping\ClassMetadata;

class FieldData
{
    protected $type;
    protected $fieldName;
    protected $entityFullClassName;

    protected $setter;
    protected $getter;
    protected $adder;

    protected $isAssoc = false;

    protected $isUnique = false;
    protected $isNullable = false;

    protected $scale;
    protected $precision;

    protected $length;

    protected $fakerMethod;
    protected $isSecurityPasswordField;

    public function __construct(string $fieldName, ClassMetadata $classMetaData, array $fieldMapping = null)
    {
        $this->setFieldName($fieldName);
        $this->setEntityFullClassName($classMetaData->getName());
        $this->setType($fieldMapping['type']);
        $this->setIsUnique($fieldMapping['unique']);
        $this->setIsNullable($fieldMapping['nullable']);

        $this->setSetter($this->guessSetterName());
        $this->setGetter($this->guessGetterName());
        $this->setAdder($this->guessAdderName());
    }


    /**
     * @return mixed
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @param mixed $type
     */
    public function setType($type): void
    {
        $this->type = $type;
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
    public function getFakerMethod()
    {
        return $this->fakerMethod;
    }

    /**
     * @param mixed $fakerMethod
     */
    public function setFakerMethod($fakerMethod): void
    {
        $this->fakerMethod = $fakerMethod;
    }

    /**
     * @return mixed
     */
    public function getisSecurityPasswordField()
    {
        return $this->isSecurityPasswordField;
    }

    /**
     * @param mixed $isSecurityPasswordField
     */
    public function setIsSecurityPasswordField($isSecurityPasswordField): void
    {
        $this->isSecurityPasswordField = $isSecurityPasswordField;
    }

    /**
     * @return mixed
     */
    public function getEntityFullClassName()
    {
        return $this->entityFullClassName;
    }

    /**
     * @param mixed $entityFullClassName
     */
    public function setEntityFullClassName($entityFullClassName): void
    {
        $this->entityFullClassName = $entityFullClassName;
    }

    /**
     * @return mixed
     */
    public function getFieldName()
    {
        return $this->fieldName;
    }

    /**
     * @param mixed $fieldName
     */
    public function setFieldName($fieldName): void
    {
        $this->fieldName = $fieldName;
    }


    /**
     * @return mixed
     */
    public function getSetter()
    {
        return $this->setter;
    }

    /**
     * @param mixed $setter
     */
    public function setSetter($setter): void
    {
        $this->setter = $setter;
    }

    /**
     * @return mixed
     */
    public function getGetter()
    {
        return $this->getter;
    }

    /**
     * @param mixed $getter
     */
    public function setGetter($getter): void
    {
        $this->getter = $getter;
    }

    /**
     * @return mixed
     */
    public function getAdder()
    {
        return $this->adder;
    }

    /**
     * @param mixed $adder
     */
    public function setAdder($adder): void
    {
        $this->adder = $adder;
    }

    /**
     * @return mixed
     */
    public function getisUnique()
    {
        return $this->isUnique;
    }

    /**
     * @param mixed $isUnique
     */
    public function setIsUnique($isUnique): void
    {
        $this->isUnique = $isUnique;
    }

    /**
     * @return mixed
     */
    public function getisNullable()
    {
        return $this->isNullable;
    }

    /**
     * @param mixed $isNullable
     */
    public function setIsNullable($isNullable): void
    {
        $this->isNullable = $isNullable;
    }

    /**
     * @return mixed
     */
    public function getScale()
    {
        return $this->scale;
    }

    /**
     * @param mixed $scale
     */
    public function setScale($scale): void
    {
        $this->scale = $scale;
    }

    /**
     * @return mixed
     */
    public function getPrecision()
    {
        return $this->precision;
    }

    /**
     * @param mixed $precision
     */
    public function setPrecision($precision): void
    {
        $this->precision = $precision;
    }

    /**
     * @return mixed
     */
    public function getLength()
    {
        return $this->length;
    }

    /**
     * @param mixed $length
     */
    public function setLength($length): void
    {
        $this->length = $length;
    }

    /**
     * @param string $className
     * @return string
     */
    protected function guessGetterName():? string
    {
        $method = "get" . $this->snakeToUpperCamelCase($this->getFieldName());
        return (method_exists($this->getEntityFullClassName(), $method)) ? $method : null;
    }

    /**
     * @param string $className
     * @return string
     */
    protected function guessSetterName():? string
    {
        $method = "set" . $this->snakeToUpperCamelCase($this->getFieldName());
        return (method_exists($this->getEntityFullClassName(), $method)) ? $method : null;
    }

    /**
     * @param string $className
     * @return string
     */
    protected function guessAdderName():? string
    {
        $method = "add" . $this->snakeToUpperCamelCase($this->getFieldName());

        if (method_exists($this->getEntityFullClassName(), $method)){
            return $method;
        }

        $method = rtrim($method, "s");
        if (method_exists($this->getEntityFullClassName(), $method)){
            return $method;
        }

        return null;
    }


    protected function snakeToUpperCamelCase(string $string): string
    {
        return str_ireplace("_", "", ucwords($string, "_"));
    }
}