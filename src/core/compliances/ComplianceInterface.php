<?php

namespace SpatialMatchIdx\core\compliances;

interface ComplianceInterface
{
    public function getMarkets():array;

    public function generateComplianceCopyrightHtml():string;

    public function getClass():string;
}
