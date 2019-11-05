<?php

namespace FakerFixtures\Faker;

use Faker\Factory;
use FakerFixtures\Doctrine\DependencyGraph;
use FakerFixtures\Doctrine\FieldData;

/**
 *
 * Help choose Faker right method based on entity's field types
 *
 * Class MethodChooser
 * @package FakerFixtures\Faker
 */
class MethodChooser
{
    /** @var \Faker\Generator */
    private $faker;

    /** @var array */
    private $fakerProperties = [];

    /** @var array */
    private $fakerMethods = [];

    /**
     * MethodChooser constructor.
     */
    public function __construct()
    {
        $this->faker = Factory::create();
        $this->findFakerPropertiesAndMethods();
    }

    /**
     *
     * Choose faker method based on field type
     *
     * @param FieldData $fieldData
     * @return string|null
     */
    public function choose(FieldData $fieldData): ?string
    {
        $method = null;
        switch($fieldData->getType()){
            case 'string':
                $method = $this->chooseString($fieldData);
                break;
            case 'text':
                $method = $this->chooseText($fieldData);
                break;
            case 'datetime':
                $method = $this->chooseDate($fieldData);
                break;
            case 'date':
                $method = $this->chooseDate($fieldData);
                break;
            case 'boolean':
                $method = 'boolean($chanceOfGettingTrue = 50)';
                break;
            case 'integer':
                $method = $this->chooseInteger($fieldData);
                break;
            case 'float':
            case 'decimal':
                $method = $this->chooseFloat($fieldData);
                break;
        }

        if ($fieldData->getisAssoc()){
            $method = "randomElement(%s)";
            if($fieldData->getType() === DependencyGraph::ONETOONE || $fieldData->getType() === DependencyGraph::MANYTOMANY) {
                $method = "unique()->$method";
            }
            if(!empty($fieldData->getJoinColumns()[0]['nullable'])){
                $method = 'optional($chancesOfValue = 0.5, $default = null)->' . $method;
            }
        }

        //add unique and optional modifier
        elseif ($method) {
            if ($fieldData->getisUnique()){
                $method = "unique()->$method";
            }
            if ($fieldData->getisNullable()) {
                $method = 'optional($chancesOfValue = 0.5, $default = null)->' . $method;
            }
        }

        return $method;
    }

    /**
     *
     * Double faker methods and props
     *
     * @param FieldData $fieldData
     * @return string
     */
    public function chooseFloat(FieldData $fieldData): string
    {
        $nbMaxDecimals = ($fieldData->getScale()) ? $fieldData->getScale() : "NULL";
        $max = ($fieldData->getPrecision()) ? str_repeat('9', $fieldData->getPrecision()) : "NULL";
        return 'randomFloat($nbMaxDecimals = '.$nbMaxDecimals.', $min = 0, $max = '.$max.')';
    }

    /**
     * Integer faker methods and props
     *
     * @param FieldData $fieldData
     * @return string
     */
    public function chooseInteger(FieldData $fieldData): string
    {
        if (strpos(mb_strtolower($fieldData->getFieldName()), "year") !== false){
            return 'year($max = "now")';
        }

        return 'numberBetween($min = 1000, $max = 9000)';
    }

    /**
     * Dates faker methods and props
     *
     * @param FieldData $fieldData
     * @return string
     */
    public function chooseDate(FieldData $fieldData): string
    {
        return 'dateTimeBetween($startDate = "- 3 months", $endDate = "now")';
    }

    /**
     * Text faker methods and props
     *
     * @param FieldData $fieldData
     * @return string
     */
    public function chooseText(FieldData $fieldData): string
    {
        return 'paragraphs($nb = $this->faker->randomDigit, $asText = true)';
    }

    /**
     * String faker methods and props
     *
     * @param FieldData $fieldData
     * @return string
     */
    public function chooseString(FieldData $fieldData): string
    {
        $fieldName = mb_strtolower($fieldData->getFieldName());
        $entityName = $fieldData->getEntityFullClassName();

        //powerful AI at work here lol

        //when length is not specified in meta data
        if (empty($fieldData->getLength())){
            $fieldData->setLength(255);
        }

        if (FakerMethodAliases::match($fieldName, 'currencyCode')){
            return 'currencyCode';
        }
        if (FakerMethodAliases::match($fieldName, 'postcode')){
            return 'postcode';
        }
        if($fieldData->getLength() <= 5){
            return 'randomLetter';
        }
        if($fieldData->getLength() <= 9){
            return 'word';
        }
        if (FakerMethodAliases::match($fieldName, 'email')){
            return 'email';
        }
        if (FakerMethodAliases::match($fieldName, 'firstName')){
            return 'firstName';
        }
        if (FakerMethodAliases::match($fieldName, 'lastName')){
            return 'lastName';
        }
        if ($entityName === 'user' && $fieldName === 'name'){
            return 'lastName';
        }
        if (FakerMethodAliases::match($fieldName, 'userName')){
            return 'userName';
        }
        if (FakerMethodAliases::match($fieldName, 'countryCode')){
            return 'countryCode';
        }
        if ($fieldData->getEntityFullClassName() === 'country' && $fieldName === 'code'){
            return 'countryCode';
        }
        if (FakerMethodAliases::match($fieldName, 'firstName')){
            return 'firstName';
        }
        if (FakerMethodAliases::match($fieldName, 'streetAddress')){
            return 'streetAddress';
        }
        if (in_array($fieldName, $this->fakerMethods)){
            return $fieldName . '()';
        }
        if (in_array($fieldName, $this->fakerProperties)){
            return $fieldData->getFieldName();
        }

        return "text({$fieldData->getLength()})";
    }

    /**
     *
     * Extract Faker properties and methods from docblocks :(
     *
     * @throws \ReflectionException
     * @return void
     */
    private function findFakerPropertiesAndMethods(): void
    {
        $fakerRc = new \ReflectionClass("\\Faker\\Generator");
        $docblock = $fakerRc->getDocComment();

        $propsRegexp = '/\*\s+@property\s+(?P<return>[a-zA-Z\\|]+)\s+\$(?P<name>\w+)\n/m';
        preg_match_all($propsRegexp, $docblock, $propsTemp);
        $this->fakerProperties = $propsTemp['name'];

        $methodsRegexp = '/\*\s+@method\s+(?P<return>.+)\s+(?P<name>\w+)\(/m';
        preg_match_all($methodsRegexp, $docblock, $methodsTemp);
        $this->fakerMethods = $methodsTemp['name'];
    }
}