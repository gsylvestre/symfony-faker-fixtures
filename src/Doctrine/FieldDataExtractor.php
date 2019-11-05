<?php

namespace FakerFixtures\Doctrine;

use Doctrine\Common\Inflector\Inflector;
use Doctrine\ORM\Mapping\ClassMetadata;
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
     * @param ClassMetadata $classMetaData
     * @return ClassData
     */
    public function getFieldsData(ClassMetadata $classMetaData, array $securityUserClass = null): ClassData
    {
        $fakerMethodChooser = new MethodChooser();
        $classData = new ClassData($classMetaData);

        //simple fields
        foreach($classMetaData->getFieldNames() as $fieldName){
            $fieldMapping = $classMetaData->getFieldMapping($fieldName);

            $field = new FieldData($fieldName, $classMetaData, $fieldMapping);
            $field->setFakerMethod($fakerMethodChooser->choose($field));

            //security user class password field?
            if (!empty($securityUserClass) && $fieldName === $securityUserClass['password_field']){
                $field->setFakerMethod(null);
                $field->setIsSecurityPasswordField(true);
            }

            $classData->addField($field);
        }

        //association fields
        foreach($classMetaData->getAssociationNames() as $associationName){
            $associationField = new AssociationFieldData($associationName, $classMetaData);
            $associationField->setFakerMethod($fakerMethodChooser->choose($associationField));
            $classData->addField($associationField);
        }

        return $classData;
    }
}