<?php

namespace SpatialMatchIdx\admin\sections\lead;

use SpatialMatchIdx\admin\sections\TabInterface;

class LeadAnalyticsTab implements TabInterface
{
    public function getTitle(): string
    {
        return '<i class="dashicons dashicons-chart-area"></i> Analytics';
    }

    public function getSlug(): string
    {
        return 'lead-analytics';
    }
}

