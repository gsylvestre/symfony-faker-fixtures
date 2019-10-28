<?php

namespace FakerFixtures\Faker;

use Faker\Factory;
use FakerFixtures\Doctrine\DepencyGraph;

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
     * @param array $fieldMetaData
     * @return string|null
     */
    public function choose(array $fieldMetaData): ?string
    {
        $method = null;
        switch($fieldMetaData['type']){
            case 'string':
                $method = $this->chooseString($fieldMetaData);
                break;
            case 'text':
                $method = $this->chooseText($fieldMetaData);
                break;
            case 'datetime':
                $method = $this->chooseDate($fieldMetaData);
                break;
            case 'date':
                $method = $this->chooseDate($fieldMetaData);
                break;
            case 'boolean':
                $method = 'boolean($chanceOfGettingTrue = 50)';
                break;
            case 'integer':
                $method = $this->chooseInteger($fieldMetaData);
                break;
            case 'float':
            case 'decimal':
                $method = $this->chooseFloat($fieldMetaData);
                break;
        }

        if ($fieldMetaData['isAssoc']){
            $method = "randomElement(%s)";
            if($fieldMetaData['type'] === DepencyGraph::ONETOONE || $fieldMetaData['type'] === DepencyGraph::MANYTOMANY) {
                $method = "unique()->$method";
            }
            if(!empty($fieldMetaData['joinColumns'][0]['nullable'])){
                $method = 'optional($chancesOfValue = 0.5, $default = null)->' . $method;
            }
        }

        //add unique and optional modifier
        elseif ($method) {
            if ($fieldMetaData['unique']){
                $method = "unique()->$method";
            }
            if ($fieldMetaData['nullable']) {
                $method = 'optional($chancesOfValue = 0.5, $default = null)->' . $method;
            }
        }

        return $method;
    }

    /**
     *
     * Double faker methods and props
     *
     * @param array $fieldMetaData
     * @return string
     */
    public function chooseFloat(array $fieldMetaData): string
    {
        $nbMaxDecimals = ($fieldMetaData['scale']) ? $fieldMetaData['scale'] : "NULL";
        $max = ($fieldMetaData['precision']) ? str_repeat('9', $fieldMetaData['precision']) : "NULL";
        return 'randomFloat($nbMaxDecimals = '.$nbMaxDecimals.', $min = 0, $max = '.$max.')';
    }

    /**
     * Integer faker methods and props
     *
     * @param array $fieldMetaData
     * @return string
     */
    public function chooseInteger(array $fieldMetaData): string
    {
        if (strpos(mb_strtolower($fieldMetaData['fieldName']), "year") !== false){
            return 'year($max = "now")';
        }

        return 'numberBetween($min = 1000, $max = 9000)';
    }

    /**
     * Dates faker methods and props
     *
     * @param array $fieldMetaData
     * @return string
     */
    public function chooseDate(array $fieldMetaData): string
    {
        return 'dateTimeBetween($startDate = "- 3 months", $endDate = "now")';
    }

    /**
     * Text faker methods and props
     *
     * @param array $fieldMetaData
     * @return string
     */
    public function chooseText(array $fieldMetaData): string
    {
        return 'paragraphs($nb = $this->faker->randomDigit, $asText = true)';
    }

    /**
     * String faker methods and props
     *
     * @param array $fieldMetaData
     * @return string
     */
    public function chooseString(array $fieldMetaData): string
    {
        $fieldName = mb_strtolower($fieldMetaData['fieldName']);
        $entityName = $fieldMetaData['entityName'];

        //powerful AI at work here lol

        //when length is not specified in meta data
        if (empty($fieldMetaData['length'])){
            $fieldMetaData['length'] = 255;
        }

        if (FakerMethodAliases::match($fieldName, 'currencyCode')){
            return 'currencyCode';
        }
        if (FakerMethodAliases::match($fieldName, 'postcode')){
            return 'postcode';
        }
        if($fieldMetaData['length'] <= 5){
            return 'randomLetter';
        }
        if($fieldMetaData['length'] <= 9){
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
        if ($fieldMetaData['entityName'] === 'country' && $fieldName === 'code'){
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
            return $fieldMetaData['fieldName'];
        }

        return "text({$fieldMetaData['length']})";
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