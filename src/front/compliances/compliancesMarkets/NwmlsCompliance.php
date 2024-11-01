<?php

namespace SpatialMatchIdx\front\compliances\compliancesMarkets;

use SpatialMatchIdx\core\compliances\ComplianceInterface;

class NwmlsCompliance implements ComplianceInterface
{
    /**
     * @var string
     */
    private $urlSlug = 'nwmls-compliance';

    public function getClass(): string
    {
        return self::class;
    }

    public function getMarkets(): array
    {
        return [
            'nwmls' => self::class,
            'arml' => self::class
        ];
    }

    /**
     * Adds the NWMLS compliance page link to the site footer
     *
     * @param $copyright
     * @return string
     */
    public function generateComplianceCopyrightHtml(): string
    {
        // build dcma notice link for adding to the footer
        $dmcaPage = site_url($this->urlSlug);
        $dcmaLink = sprintf('<a href="%s" title="DMCA Notice" target="_blank">DMCA Notice</a>', $dmcaPage);

        return ' ' . $dcmaLink;
    }
}
