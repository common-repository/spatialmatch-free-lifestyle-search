<?php

namespace SpatialMatchIdx\admin\sections;

class LicenseSettings implements TabInterface
{
    public function getTitle(): string
    {
        return 'License Key';
    }

    public function getSlug(): string
    {
        return 'license';
    }
}
