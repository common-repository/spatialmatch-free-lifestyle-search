<?php

namespace SpatialMatchIdx\core\virtualPages\front;

interface VirtualPageInterface
{
    public function getSlug(): string;

    public function execute();
}
