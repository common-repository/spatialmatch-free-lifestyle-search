<?php

namespace SpatialMatchIdx\admin\sections\lead;

use SpatialMatchIdx\admin\sections\TabInterface;

class LeadDetailTab implements TabInterface
{
    public function getTitle(): string
    {
        return '<i class="dashicons dashicons-id-alt"></i> Contact';
    }

    public function getSlug(): string
    {
        return 'lead-detail';
    }
}
