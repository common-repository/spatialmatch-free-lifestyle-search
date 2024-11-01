<?php

namespace SpatialMatchIdx\admin\sections\lead;

use SpatialMatchIdx\admin\sections\TabInterface;

class LeadSearchesTab implements TabInterface
{
    public function getTitle(): string
    {
        return '<i class="dashicons dashicons-search"></i> Searches';
    }

    public function getSlug(): string
    {
        return 'lead-searches';
    }
}

