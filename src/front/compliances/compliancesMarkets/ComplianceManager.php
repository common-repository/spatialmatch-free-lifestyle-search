<?php

namespace SpatialMatchIdx\front\compliances\compliancesMarkets;

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

    public function addCompliance(ComplianceInterface $compliance)
    {
        $this->compliances[$compliance->getSlug()] = $compliance;
    }

    /**
     * @return ComplianceInterface[]
     */
    public function getCompliances(): array
    {
        return $this->compliances;
    }

    public function getCompliance(string $complianceSlug): ComplianceInterface
    {
        return $this->compliances[$complianceSlug];
    }

    public function isMarketsComplianceBySlug(string $complianceSlug, array $licenseMarkets): bool
    {
        $compliance = $this->getCompliance($complianceSlug);
        $complianceMarkets = $compliance->getMarkets();

        foreach ($licenseMarkets as $market) {
            if (isset($market['id'], $complianceMarkets[$market['id']])) {
                return true;
            }
        }

        return false;
    }

    public function getCompliancesMarketsByLicense(string $complianceSlug, array $licenseMarkets): array
    {
        $compliance = $this->getCompliance($complianceSlug);
        $complianceMarkets = $compliance->getMarkets();

        $result = [];

        foreach ($licenseMarkets as $market) {
            if (isset($market['id'], $complianceMarkets[$market['id']])) {
                $result[$market['id']] = $complianceMarkets[$market['id']];
            }
        }

        return  $result;
    }

    public function isComplianceMarkets(array $licenseMarkets): bool
    {
        $compliances = $this->getCompliances();

        foreach ($compliances as $compliance) {
            if ($this->isMarketsComplianceBySlug($compliance->getSlug(), $licenseMarkets)) {
                return true;
            }
        }

        return false;
    }

    public function getAfterDisclaimerCopyright(string $complianceSlug): string
    {
        $compliance = $this->getCompliance($complianceSlug);

        return $compliance->generateComplianceCopyrightHtml();
    }
}
