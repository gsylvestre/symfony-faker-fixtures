<?php

namespace FakerFixtures\Faker;


class FakerAliases
{
    const FAKER_METHOD_ALIASES = [
        'postcode' =>
            ['zip', 'postalcode', 'postal_code', 'postcode', 'post_code'],
        'currencyCode' =>
            ['curr', 'currency', 'currencycode'],
        'email' =>
            ['email', 'mail', 'email_address', 'mail_address'],
        'firstName' =>
            ['firstname', 'first_name'],
        'lastName' =>
            ['lastname', 'last_name'],
        'userName' =>
            ['username', 'user_name'],
        'countryCode' =>
            ['country', 'countrycode', 'country_code'],
        'streetName' =>
            ['street', 'streetname', 'street_name'],
        'streetAddress' =>
            ['streetaddress', 'street_address', 'address'],
        'ipv4' =>
            ['ip', 'ip_address', 'ipaddress', 'ip_v4'],
        'url' =>
            ['url', 'uri'],
    ];

    const ENTITY_NAME_ALIASES = [
        'Country' =>
            ['Country']
    ];


    /**
     * @param string $fieldName
     * @param string $againstMethod
     * @return bool
     */
    public static function matchField(string $fieldName, string $againstMethod): bool
    {
        return self::match($fieldName, $againstMethod, self::FAKER_METHOD_ALIASES);
    }

    /**
     * @param string $entityName
     * @param string $againstClassName
     * @return bool
     */
    public static function matchEntity(string $entityName, string $againstClassName): bool
    {
        return self::match($entityName, $againstClassName, self::ENTITY_NAME_ALIASES);
    }

    /**
     * @param string $needle
     * @param string $against
     * @param array $in
     * @return bool
     */
    private static function match(string $needle, string $against, array $in): bool
    {
        $needle = mb_strtolower($needle);

        if ($needle === $against) {
            return true;
        }

        if (!array_key_exists($against, $in)){
            return false;
        }

        return in_array($needle, $in[$against]);
    }
}