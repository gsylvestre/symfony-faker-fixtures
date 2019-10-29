<?php

namespace FakerFixtures\Doctrine;

use Doctrine\ORM\Mapping\ClassMetadata;

class AssociationHelper
{

    /**
     * get all pivot table names (for truncation)
     *
     * @param ClassMetadata $classMetaData
     * @return array
     */
    public static function getPivotTableNames(ClassMetadata $classMetaData): array
    {
        $names = [];
        foreach($classMetaData->getAssociationMappings() as $property => $associationInfo){
            if (!empty($associationInfo['joinTable']['name'])){
                $names[] = $associationInfo['joinTable']['name'];
            }
        }

        return $names;
    }

}