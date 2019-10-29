<?php

namespace FakerFixtures\Security;

use Symfony\Bundle\MakerBundle\FileManager;
use Symfony\Bundle\MakerBundle\Util\YamlSourceManipulator;

/**
 * Class UserClassHelper
 * @package FakerFixtures\Security
 */
class UserClassHelper
{
    /** @var FileManager */
    private $fileManager;

    /**
     * @param FileManager $fileManager
     */
    public function __construct(FileManager $fileManager)
    {
        $this->fileManager = $fileManager;
    }

    /**
     * @param string $entityFullName
     * @return bool
     */
    public function isSecurityUserClass(string $entityFullName):bool
    {
        return $entityFullName === $this->getUserClassFromConfig();
    }

    /**
     * @return array|null
     */
    public function getUserClassInfos(): ?array
    {
        $userClass = $this->getUserClassFromConfig();
        if ($userClass){
            return [
                "class_name" => $userClass,
                "password_field" => (property_exists($userClass, 'password')) ? 'password' : null,
            ];
        }

        return null;
    }

    /**
     * @return array|null
     */
    private function getUserClassFromConfig(): ?string
    {
        if ($this->fileManager->fileExists($path = 'config/packages/security.yaml')) {
            $manipulator = new YamlSourceManipulator($this->fileManager->getFileContents($path));
            $securityData = $manipulator->getData();
            $providersData = $securityData['security']['providers'] ?? [];
            if (1 === \count($providersData) && isset(current($providersData)['entity'])) {
                $entityProvider = current($providersData);
                return $entityProvider['entity']['class'];
            }
        }

        return null;
    }
}