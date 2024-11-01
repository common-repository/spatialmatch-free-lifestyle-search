<?php

namespace SpatialMatchIdx\admin\sections;

class ComplianceSettings implements TabInterface
{
    public function getTitle(): string
    {
        return 'Compliance';
    }

    public function getSlug(): string
    {
        return 'compliance';
    }
}
