<?php

namespace FakerFixtures\Faker;


class FakerAliases
{
    const FAKER_METHOD_ALIASES = [
        'postcode' => [
            'fields' => ['zip', 'postalcode', 'postal_code', 'postcode', 'post_code'],
        ],
        'currencyCode' => [
            'fields' => ['curr', 'currency', 'currencycode']
        ],
        'email' => [
            'fields' => ['email', 'mail', 'email_address', 'mail_address'],
        ],
        'firstName' => [
            'fields' => ['firstname', 'first_name'],
        ],
        'lastName' => [
            'fields' => ['lastname', 'last_name'],
            'fieldsWithinEntity' => [
                'user' => ['name']
            ]
        ],
        'userName' =>[
            'fields' => ['username', 'user_name'],
        ],
        'countryCode' => [
            'fields' => ['country', 'countrycode', 'country_code'],
            'fieldsWithinEntity' => [
                'country' => ['code']
            ]
        ],
        'streetName' => [
            'fields' => ['street', 'streetname', 'street_name'],
        ],
        'streetAddress' => [
            'fields' => ['streetaddress', 'street_address', 'address'],
        ],
        'ipv4' => [
            'fields' => ['ip', 'ip_address', 'ipaddress', 'ip_v4'],
        ],
        'url' => [
            'fields' => ['url', 'uri'],
        ],
        'word' => [
            'fields' => ['tagname', 'tag_name', 'category_name', 'categoryname'],
            'fieldsWithinEntity' => [
                'tag' => ['name'],
                'category' => ['name'],
            ]
        ],
    ];

    /**
     * @param string $fieldName
     * @param string $againstFakerMethod
     * @return bool
     */
    public static function matchField(string $fieldName, string $againstFakerMethod, string $entityName = null): bool
    {
        $needle = mb_strtolower($fieldName);
        $entityName = mb_strtolower($entityName);

        if ($needle === $againstFakerMethod) {
            return true;
        }

        if (!array_key_exists($againstFakerMethod, self::FAKER_METHOD_ALIASES)) {
            return false;
        }

        //check for both entity name match AND field match first
        if ($entityName && 
            !empty(self::FAKER_METHOD_ALIASES[$againstFakerMethod]['fieldsWithinEntity']) &&
            array_key_exists($entityName, self::FAKER_METHOD_ALIASES[$againstFakerMethod]['fieldsWithinEntity'])) {
            if (in_array($needle, self::FAKER_METHOD_ALIASES[$againstFakerMethod]['fieldsWithinEntity'][$entityName])) {
                return true;
            }
        }

        return in_array($needle, self::FAKER_METHOD_ALIASES[$againstFakerMethod]['fields']);
    }
}