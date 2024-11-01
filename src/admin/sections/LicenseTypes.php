<?php

namespace SpatialMatchIdx\admin\sections;

class LicenseTypes implements TabInterface
{
    public function getTitle(): string
    {
        return 'License Types';
    }

    public function getSlug(): string
    {
        return 'license-types';
    }
}
