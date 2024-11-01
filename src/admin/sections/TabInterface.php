<?php

namespace SpatialMatchIdx\admin\sections;

interface TabInterface
{
    public function getTitle(): string;

    public function getSlug(): string;
}
