<?php

namespace Majkrzak\KubernetesConfig\Service;

use KubernetesClient\Client;
use KubernetesClient\Config;
use Majkrzak\KubernetesConfig\Data\ConfigEntry;

class KubernetesApi
{
    private readonly ?Client $client;
    private readonly ?string $podNamespace;
    private readonly ?string $podName;

    private const ANNOTATION_PREFIX = "magento.config/";
    private const NAMESPACE_FILE = "/var/run/secrets/kubernetes.io/serviceaccount/namespace";

    public function __construct()
    {
        try {
            $this->client = new Client(
                Config::LoadFromDefault()
            );
        } catch (\Error $e) {
            $this->client = null;
        }

        $this->podNamespace = \getenv("POD_NAMESPACE") ?:
            (\file_exists(self::NAMESPACE_FILE) ? \file_get_contents(self::NAMESPACE_FILE) : null);

        $this->podName = \getenv("POD_NAME") ?:
            \gethostname() ?: null;
    }

    /**
     * Tests if Kubernetes API is properly initialized.
     */
    public function isActive(): bool
    {
        return $this->client != null && $this->podNamespace != null && $this->podName != null;
    }

    /**
     * Fetch `magento.config/*` annotations of current Pod and
     *  all its ancestors and converts them to ConfigEntries.
     */
    public function parseAnnotations(): \Generator
    {
        if (!$this->isActive()) {
            return;
        }

        $f = function (
            string $apiVersion,
            string $kind,
            string $name,
        ) use (&$f): \Generator {

            $scope = !\str_contains($apiVersion, "/") ? "api" : "apis";

            $plural = \array_values(
                \array_filter(
                    $this->client->request("/{$scope}/{$apiVersion}")["resources"],
                    function ($resource) use ($kind) {
                        return $resource["kind"] == $kind && !\str_contains($resource["name"], "/");
                    }
                )
            )[0]["name"];

            $data = $this->client->request("/{$scope}/{$apiVersion}/namespaces/{$this->podNamespace}/{$plural}/{$name}");

            foreach ((($data["metadata"] ?? []) ["annotations"] ?? []) as $key => $val) {
                if (\str_starts_with($key, KubernetesApi::ANNOTATION_PREFIX)) {
                    yield ConfigEntry::from($key, $val);
                }
            }

            foreach ((($data["metadata"] ?? []) ["ownerReferences"] ?? []) as $ownerReference) {
                yield from $f(
                    $ownerReference["apiVersion"],
                    $ownerReference["kind"],
                    $ownerReference["name"],
                );
            }
        };

        yield from $f(
            "v1",
            "Pod",
            $this->podName,
        );
    }
}
