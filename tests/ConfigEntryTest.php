<?php

use PHPUnit\Framework\TestCase;
use Majkrzak\KubernetesConfig\Data\ConfigEntry;

final class ConfigEntryTest extends TestCase
{
    public function testModifiesExistingField(): void
    {
        $config = ["foo" => "old"];
        new ConfigEntry(["foo"], "new") -> applyTo($config);
        $this->assertEquals(["foo" => "new"], $config);
    }

    public function testCreatesNewFiled(): void
    {
        $config = [];
        new ConfigEntry(["foo"], "new") -> applyTo($config);
        $this->assertEquals(["foo" => "new"], $config);
    }

    public function testModifiesExistingNestedField(): void
    {
        $config = ["foo" => ["bar" => "old"]];
        new ConfigEntry(["foo","bar"], "new") -> applyTo($config);
        $this->assertEquals(["foo" => ["bar" => "new"]], $config);
    }

    public function testCreatesNewNestedField(): void
    {
        $config = ["foo" => []];
        new ConfigEntry(["foo","bar"], "new") -> applyTo($config);
        $this->assertEquals(["foo" => ["bar" => "new"]], $config);
    }

    public function testCreatesNewNestedChainedField(): void
    {
        $config = [];
        new ConfigEntry(["foo","bar"], "new") -> applyTo($config);
        $this->assertEquals(["foo" => ["bar" => "new"]], $config);
    }

    public function testParsesPlain(): void
    {
        $this->assertEquals(
            new ConfigEntry(["foo","bar"], "val"),
            ConfigEntry::from("foo.bar", "val")
        );
    }

    public function testParsesAnnotation(): void
    {
        $this->assertEquals(
            new ConfigEntry(["foo","bar"], "val"),
            ConfigEntry::from("example.com/foo.bar", "val")
        );
    }
}
