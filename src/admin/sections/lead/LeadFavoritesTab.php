<?php

namespace SpatialMatchIdx\admin\sections\lead;

use SpatialMatchIdx\admin\sections\TabInterface;

class LeadFavoritesTab implements TabInterface
{
    public function getTitle(): string
    {
        return '<i class="dashicons dashicons-heart"></i> Favorites';
    }

    public function getSlug(): string
    {
        return 'lead-favorites';
    }
}

