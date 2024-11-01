<?php

namespace SpatialMatchIdx\admin\sections;

class MapSettings implements TabInterface
{
    public function getTitle(): string
    {
        return 'Map';
    }

    public function getSlug(): string
    {
        return 'map';
    }
}
