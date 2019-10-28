<?php

namespace FakerFixtures\Faker;


class FakerMethodAliases
{
    const ALIASES = [
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
    ];

    /**
     * @param string $fieldName
     * @param string $againstMethod
     * @return bool
     */
    public static function match(string $fieldName, string $againstMethod): bool
    {
        return in_array(mb_strtolower($fieldName), self::ALIASES[$againstMethod]);
    }
}