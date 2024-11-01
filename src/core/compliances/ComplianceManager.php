<?php

namespace SpatialMatchIdx\core\compliances;

use SpatialMatchIdx\services\LicenseService;

class ComplianceManager
{
    /**
     * @var array
     */
    private static $instances = [];

    /**
     * @var array
     */
    private $compliances = [];

    private function __construct()
    {
    }

    /**
     * @return ComplianceManager
     */
    public static function getInstance(): ComplianceManager
    {
        $cls = static::class;
        if (!isset(self::$instances[$cls])) {
            self::$instances[$cls] = new static();
        }

        return self::$instances[$cls];
    }

    /**
     * @param ComplianceInterface $compliance
     */
    public function addCompliance(ComplianceInterface $compliance)
    {
        $this->compliances[get_class($compliance)] = $compliance;
    }

    /**
     * @return ComplianceInterface[]
     */
    public function getCompliances(): array
    {
        return $this->compliances;
    }

    public function getCompliance(string $complianceClass): ComplianceInterface
    {
        return $this->compliances[$complianceClass];
    }

    /**
     * @param string $compliance
     * @param array $licenseMarkets
     * @return bool
     */
    public function isMarketsComplianceBySlug(string $compliance, array $licenseMarkets): bool
    {
        $complianceMarkets = (new $compliance)->getMarkets();

        foreach ($licenseMarkets as $market) {
            if (isset($market['id'], $complianceMarkets[$market['id']])) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param array $licenseMarkets
     * @return bool
     */
    public function isComplianceMarkets(array $licenseMarkets): bool
    {
        $compliances = $this->getCompliances();

        foreach ($compliances as $compliance) {
            if ($this->isMarketsComplianceBySlug($compliance->getClass(), $licenseMarkets)) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param string $copyright
     * @return string
     */
    public function getAfterDisclaimerCopyright($copyright = ''): string
    {
        $licenseMarkets = LicenseService::getInstance()->getMarkets();

        if (is_array($licenseMarkets)) {
            foreach ($this->getCompliances() as $compliance) {
                if ($this->isMarketsComplianceBySlug(get_class($compliance), $licenseMarkets)) {
                    $copyright .= $compliance->generateComplianceCopyrightHtml();
                }
            }
        }

        return $copyright;
    }
}
