<?php

namespace App\Tests;

use FakerFixtures\Doctrine\AssociationFieldData;
use FakerFixtures\Doctrine\DependencyGraph;
use FakerFixtures\Doctrine\FieldDataExtractor;

class FieldDataExtractorTest extends FakerFixtureTestCase
{

    public function testEntitiesAreLoaded()
    {
        $this->assertEquals(9, count($this->metadatas));
    }

    public function testClassDatasAreExtracted()
    {
        $fieldDataExtractor = new FieldDataExtractor();

        foreach($this->metadatas as $metadata) {
            $classData = $fieldDataExtractor->getFieldsData($metadata);
            $this->assertNotEmpty($classData->getShortClassName());
            $this->assertNotEmpty($classData->getFullClassName());
        }
    }

    public function testFieldDatasAreExtracted()
    {
        $fieldDataExtractor = new FieldDataExtractor();

        foreach($this->metadatas as $metadata) {
            $classData = $fieldDataExtractor->getFieldsData($metadata);
            $this->assertNotEmpty($classData->getFields());

            /** @var $fieldData \FakerFixtures\Doctrine\FieldData|AssociationFieldData * */
            foreach ($classData->getFields() as $fieldData) {
                $this->assertNotEmpty($fieldData->getFieldName());
                $this->assertNotEmpty($fieldData->getType());

                if ($fieldData->getType() !== DependencyGraph::MANYTOMANY
                    && $fieldData->getType() !== DependencyGraph::ONETOMANY
                    && $fieldData->getType() !== "json") {
                    $this->assertNotEmpty($fieldData->getFakerMethod(), "faker method not found for " . $fieldData->getEntityFullClassName() . ":" . $fieldData->getFieldName());
                }

                if ($fieldData->getFieldName() != "id"
                    && $fieldData->getType() !== DependencyGraph::MANYTOMANY
                    && $fieldData->getType() !== DependencyGraph::ONETOMANY) {
                    $this->assertNotEmpty($fieldData->getSetter(), "setter not found for " . $fieldData->getEntityFullClassName() . ":" . $fieldData->getFieldName());
                }
                $this->assertNotEmpty($fieldData->getGetter());
                $this->assertNotEmpty($fieldData->getEntityFullClassName());
                $this->assertIsBool($fieldData->getisAssoc());

                if ($fieldData->getisAssoc() && $fieldData->getisOwningSide()){
                    $this->assertNotEmpty($fieldData->getAssociatedFullClassName(), $fieldData->getEntityFullClassName() . ":" . $fieldData->getFieldName());
                    $this->assertNotEmpty($fieldData->getAssociatedShortClassName(), $fieldData->getEntityFullClassName() . ":" . $fieldData->getFieldName());
                    $this->assertNotEmpty($fieldData->getAssociatedShortPluralClassName(), $fieldData->getEntityFullClassName() . ":" . $fieldData->getFieldName());
                }
            }
        }
    }
}