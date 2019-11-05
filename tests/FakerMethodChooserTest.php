<?php

namespace App\Tests;

use Doctrine\ORM\Mapping\ClassMetadata;
use FakerFixtures\Doctrine\AssociationFieldData;
use FakerFixtures\Doctrine\DependencyGraph;
use FakerFixtures\Doctrine\FieldData;
use FakerFixtures\Doctrine\FieldDataExtractor;
use FakerFixtures\Faker\MethodChooser;

class FakerMethodChooserTest extends FakerFixtureTestCase
{

    public function testFakerFieldMethodsFromEntities()
    {
        $fieldDataExtractor = new FieldDataExtractor();

        foreach($this->metadatas as $metadata) {
            $classData = $fieldDataExtractor->getFieldsData($metadata);
            $this->assertNotEmpty($classData->getFields());

            /** @var $fieldData \FakerFixtures\Doctrine\FieldData|AssociationFieldData * */
            foreach ($classData->getFields() as $fieldData) {
                if ($fieldData->getFieldName() != "roles") {
                    $this->assertNotEmpty($fieldData->getFakerMethod(), $fieldData->getEntityFullClassName() . ":" . $fieldData->getFieldName());
                }
            }
        }
    }

    /**
     * @dataProvider provider
     */
    public function testFakerMethodsFromRawDatas($expectedMethod, $fieldName, $fieldType, $fieldLength, $className)
    {
        $stub = $this->createMock(FieldData::class);
        $stub->method('getFieldName')->willReturn($fieldName);
        $stub->method('getType')->willReturn($fieldType);
        $stub->method('getLength')->willReturn($fieldLength);
        $stub->method('getEntityFullClassName')->willReturn('App\Entity\\' . $className);

        $fakerMethodChooser = new MethodChooser();
        $fakerMethod = $fakerMethodChooser->choose($stub);
        $this->assertIsString($fakerMethod, $stub->getEntityFullClassName() . ":" . $stub->getFieldName());
        $this->assertEquals($expectedMethod, $fakerMethod);
    }

    public function provider()
    {
        yield ["text(125)", "test", "string", 125, "Test"];
        yield ["text(12)", "test", "string", 12, "Test"];
        yield ['dateTimeBetween($startDate = "- 3 months", $endDate = "now")', "whatever", "datetime", null, "Test"];
        yield ['randomFloat($nbMaxDecimals = NULL, $min = 0, $max = NULL)', 'price', 'float', null, 'Whatever'];
    }
}