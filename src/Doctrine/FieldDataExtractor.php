<?php

namespace FakerFixtures\Doctrine;

use Doctrine\Common\Persistence\Mapping\ClassMetadata;
use FakerFixtures\Faker\MethodChooser;

/**
 * Load entity field datas in easy to use data structure
 *
 * Class FieldDataExtractor
 * @package FakerFixtures\Doctrine
 */
class FieldDataExtractor
{
    /**
     * Prepare each field data
     *
     * @param ClassMetadata $classMetadata
     * @return array
     */
    public function getFieldsData(ClassMetadata $classMetadata, array $securityUserClass = null): array
    {
        //help choose best faker method
        $fakerMethodChooser = new MethodChooser();

        $fields = [];
        foreach($classMetadata->getFieldNames() as $fieldName){
            $field = $classMetadata->getFieldMapping($fieldName);

            $field['isAssoc'] = false;
            $field['setter'] = $this->guessSetterName($classMetadata->getName(), $fieldName);
            $field['getter'] = $this->guessGetterName($classMetadata->getName(), $fieldName);
            $field['entityName'] = $classMetadata->getName();
            $field['fakerMethod'] = $fakerMethodChooser->choose($field);

            //security user class password field?
            if (!empty($securityUserClass) && $fieldName === $securityUserClass['password_field']){
                $field['fakerMethod'] = null;
                $field["isSecurityPasswordField"] = true;
            }
            $fields[] = $field;
        }

        $associationNames = $classMetadata->getAssociationNames();
        foreach($associationNames as $associationName){
            /** @var array $association */
            $association = $classMetadata->getAssociationMapping($associationName);

            if (DepencyGraph::isADependantAssociation($association)){
                $field = $association;
                $field['assocShortClassName'] = $boundClass = (new \ReflectionClass($association['targetEntity']))->getShortName();
                $field['isAssoc'] = true;
                if ($field['type'] === DepencyGraph::ONETOONE || $field['type'] === DepencyGraph::MANYTOONE){
                    $field['setter'] = $this->guessSetterName($classMetadata->getName(), $associationName);
                }
                else {
                    $field['adder'] = $this->guessAdderName($classMetadata->getName(), $associationName);
                }
                $field['fakerMethod'] = $fakerMethodChooser->choose($field);

                $fields[] = $field;
            }
        }

        return $fields;
    }

    /**
     * @param string $className
     * @return string
     */
    private function guessGetterName(string $className, string $fieldName):? string
    {
        $method = "get" . $this->snakeToUpperCamelCase($fieldName);
        return (method_exists($className, $method)) ? $method : null;
    }

    /**
     * @param string $className
     * @return string
     */
    private function guessSetterName(string $className, string $fieldName):? string
    {
        $method = "set" . $this->snakeToUpperCamelCase($fieldName);
        return (method_exists($className, $method)) ? $method : null;
    }

    /**
     * @param string $className
     * @return string
     */
    private function guessAdderName(string $className, string $fieldName):? string
    {
        $method = "add" . $this->snakeToUpperCamelCase($fieldName);

        if (method_exists($className, $method)){
            return $method;
        }

        $method = rtrim($method, "s");
        if (method_exists($className, $method)){
            return $method;
        }

        return null;
    }

    private function snakeToUpperCamelCase(string $string): string
    {
        return str_ireplace("_", "", ucwords($string, "_"));
    }
}