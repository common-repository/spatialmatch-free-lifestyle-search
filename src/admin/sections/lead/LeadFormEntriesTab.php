<?php

namespace SpatialMatchIdx\admin\sections\lead;

use SpatialMatchIdx\admin\sections\TabInterface;

class LeadFormEntriesTab implements TabInterface
{
    public function getTitle(): string
    {
        return '<i class="dashicons dashicons-admin-comments"></i> Form Entries';
    }

    public function getSlug(): string
    {
        return 'lead-form-entries';
    }
}

