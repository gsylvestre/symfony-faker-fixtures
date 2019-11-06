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
        $stub->method('getEntityShortClassName')->willReturn($className);

        $fakerMethodChooser = new MethodChooser();
        $fakerMethod = $fakerMethodChooser->choose($stub);
        $this->assertIsString($fakerMethod, $stub->getEntityFullClassName() . ":" . $stub->getFieldName());
        $this->assertEquals($expectedMethod, $fakerMethod);
    }

    public function provider()
    {
        //expected faker method, field name, type, length, class name

        //simple strings
        yield ['text(125)', 'test', 'string', 125, 'Test'];
        yield ['text(12)', 'test', 'string', 12, 'Test'];

        //dates
        yield ['dateTimeBetween($startDate = "- 3 months", $endDate = "now")', 'whatever', 'datetime', null, 'Test'];

        //numbers
        yield ['randomFloat($nbMaxDecimals = NULL, $min = 0, $max = NULL)', 'price', 'float', null, 'Whatever'];

        //specific strings
        yield ['countryCode', 'countryCode', 'string', 2, 'Country'];
        yield ['countryCode', 'code', 'string', 2, 'Country'];
        //title will return a Mr, Ms... which is not good. We want a sentence
        yield ['sentence($nbWords = $this->faker->randomDigit, $variableNbWords = false)', 'title', 'string', 50, 'Whatever'];
        yield ['userName', 'username', 'string', 30, 'Whatever'];
        yield ['userName', 'userName', 'string', 30, 'Whatever'];
        yield ['userName', 'user_name', 'string', 30, 'Whatever'];
        yield ['lastName', 'name', 'string', 30, 'User'];
        yield ['email', 'email', 'string', 255, 'Whatever'];
        yield ['firstName', 'first_name', 'string', 255, 'Whatever'];
        yield ['firstName', 'firstname', 'string', 255, 'Whatever'];
        yield ['lastName', 'last_name', 'string', 255, 'Whatever'];
        yield ['lastName', 'lastname', 'string', 255, 'Whatever'];
        yield ['ipv4', 'ip', 'string', 120, 'Whatever'];
        yield ['url', 'url', 'string', 255, 'Whatever'];
        yield ['url', 'uri', 'string', 255, 'Whatever'];
        yield ['url', 'uri', 'text', null, 'Whatever'];

    }
}