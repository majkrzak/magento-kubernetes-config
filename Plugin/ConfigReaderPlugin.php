<?php

namespace Majkrzak\KubernetesConfig\Plugin;

class ConfigReaderPlugin
{
    public function afterLoad(Magento\Framework\App\DeploymentConfig\Reader $subject, array $result, $fileKey = null)
    {
        if ($fileKey != null) {
            return $result;
        }

        return $result;
    }
}
