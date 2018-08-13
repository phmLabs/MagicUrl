<?php

namespace phmLabs\MagicUrl\Rule;

interface Rule
{
    public function resolve($urlString);
}