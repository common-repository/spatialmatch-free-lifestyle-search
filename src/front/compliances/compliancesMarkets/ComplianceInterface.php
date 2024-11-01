<?php

namespace SpatialMatchIdx\front\compliances\compliancesMarkets;

interface ComplianceInterface
{
    public function getMarkets():array;

    public function generateComplianceCopyrightHtml():string;

    public function initHooks();

    public function getSlug():string;
}
