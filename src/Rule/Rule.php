<?php

namespace phmLabs\MagicUrl\Rule;

interface Rule
{
    /**
     * @param $urlString
     * @return mixed
     * @throws ResolveException
     */
    public function resolve($urlString);
}