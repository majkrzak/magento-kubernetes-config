<?php

namespace Majkrzak\KubernetesConfig\Data;

class ConfigEntry
{
    /**
     * @param array<int,string> $path
     * @param string $value
     */
    public function __construct(
        public readonly array $path,
        public readonly string $value,
    ) {
    }

    /**
     * Apply this entry to given array
     *
     * @param array $ptr
     */
    public function apply(array &$ptr): void
    {
        foreach ($this->path as $key) {
            if (!\array_key_exists($key, $ptr)) {
                $ptr[$key] = [];
            }
            $ptr = &$ptr[$key];
        }

        $ptr = $this->value;
    }
}
