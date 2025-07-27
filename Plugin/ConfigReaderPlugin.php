<?php

namespace Majkrzak\KubernetesConfig\Plugin;

class ConfigReaderPlugin
{
    public function __construct(
        public readonly \Majkrzak\KubernetesConfig\Service\KubernetesApi $kubernetesApi,
    ) {
    }

    public function afterLoad(
        \Magento\Framework\App\DeploymentConfig\Reader $subject,
        array $result,
        $fileKey = null,
    ) {
        if ($fileKey != null) {
            return $result;
        }

        if (!$this->kubernetesApi->isActive()) {
            return $result;
        }

        foreach ($this->kubernetesApi->parseAnnotations() as $annotation) {
            $annotation->applyTo($result);
        }

        return $result;
    }
}
