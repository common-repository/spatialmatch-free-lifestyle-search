<?php

namespace SpatialMatchIdx\admin\sections;

class GeneralSettings implements TabInterface
{
    public function getTitle(): string
    {
        return 'General';
    }

    public function getSlug(): string
    {
        return 'general';
    }
}
