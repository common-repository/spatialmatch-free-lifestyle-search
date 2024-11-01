<?php

namespace SpatialMatchIdx\front\compliances\compliancesMarkets;

use SpatialMatchIdx\core\compliances\ComplianceInterface;

class NnerenCompliance implements ComplianceInterface
{
    public function getMarkets(): array
    {
        return [
            'nneren' => self::class,
        ];
    }

    public function getClass(): string
    {
        return self::class;
    }

    public function generateComplianceCopyrightHtml(): string
    {
        return '<div class="idx-compliance"><p class="idx-footnotes">Schools, area information and recent sales are provided by Home Junction Inc.</p></div>';
    }
}
