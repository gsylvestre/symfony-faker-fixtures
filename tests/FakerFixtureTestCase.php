<?php

namespace App\Tests;

use Doctrine\ORM\Tools\Setup;
use Doctrine\ORM\EntityManager;
use PHPUnit\Framework\TestCase;

class FakerFixtureTestCase extends TestCase
{
    /** @var EntityManager */
    protected $em;

    protected $metadatas;

    public function setUp(): void
    {
        // Create a simple "default" Doctrine ORM configuration for Annotations
        $isDevMode = true;
        $proxyDir = null;
        $cache = null;
        $useSimpleAnnotationReader = false;
        $config = Setup::createAnnotationMetadataConfiguration(array(__DIR__."/fixtures/src/Entity"), $isDevMode, $proxyDir, $cache, $useSimpleAnnotationReader);

        // database configuration parameters
        $conn = array(
            'driver' => 'pdo_sqlite',
            'path' => __DIR__ . '/db.sqlite',
        );

        // obtaining the entity manager
        $this->em = EntityManager::create($conn, $config);
        $this->metadatas = $this->em->getMetadataFactory()->getAllMetadata();
    }

}