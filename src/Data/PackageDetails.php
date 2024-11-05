<?php

namespace JordanPartridge\Packagist\Data;

use Exception;
use Illuminate\Support\Collection;
use InvalidArgumentException;
use RuntimeException;
use JsonException;
use Composer\MetadataMinifier\MetadataMinifier;
use JordanPartridge\Packagist\Contracts\DataTransferObjectInterface;

/**
 * Represents detailed package information from Packagist
 */
readonly class PackageDetails implements DataTransferObjectInterface
{
    private Collection $processedPackages;
    private Collection $processedAdvisories;

    public function __construct(
        private string $minified,
        private array $packages,
        private array $security_advisories,
        private ?string $lastModified = null,
    ) {
        $this->processedPackages = collect($this->packages);
        $this->processedAdvisories = collect($this->security_advisories);
    }

    /**
     * Get the minified data string
     */
    public function minified(): string
    {
        return $this->minified;
    }

    /**
     * Get all security advisories as a collection
     */
    public function securityAdvisories(): Collection
    {
        return $this->processedAdvisories->map(function ($advisory) {
            return [
                'packageName' => $advisory['packageName'] ?? null,
                'title' => $advisory['title'] ?? null,
                'link' => $advisory['link'] ?? null,
                'cve' => $advisory['cve'] ?? null,
                'affectedVersions' => $advisory['affectedVersions'] ?? [],
                'reportedAt' => $advisory['reportedAt'] ?? null,
            ];
        });
    }

    /**
     * Get all packages as a collection
     */
    public function packages(): Collection
    {
        return $this->processedPackages;
    }

    /**
     * Get the last modified timestamp
     */
    public function lastModified(): ?string
    {
        return $this->lastModified;
    }

    /**
     * Find a specific package by name
     *
     * @throws Exception
     */
    public function findPackage(string $name): array
    {
        $package = $this->processedPackages->get($name);

        if (!$package) {
            throw new Exception("Package not found: {$name}");
        }

        return $package;
    }

    /**
     * Get security advisories for a specific package
     */
    public function getPackageAdvisories(string $packageName): Collection
    {
        return $this->securityAdvisories()
            ->filter(fn ($advisory) => $advisory['packageName'] === $packageName);
    }

    /**
     * Expand minified package metadata
     *
     * @throws RuntimeException
     */
    public function expanded(): array
    {
        try {
            if (!isset($this->packages)) {
                throw new Exception('Invalid package data structure');
            }

            return MetadataMinifier::expand($this->packages);
        } catch (Exception $e) {
            throw new RuntimeException("Failed to expand package metadata: {$e->getMessage()}");
        }
    }

    /**
     * Convert package details to JSON
     *
     * @throws JsonException
     */
    public function toJson(int $flags = 0): string
    {
        try {
            return json_encode([
                'minified' => $this->minified,
                'packages' => $this->packages,
                'security_advisories' => $this->security_advisories,
                'last_modified' => $this->lastModified,
            ], JSON_THROW_ON_ERROR | $flags);
        } catch (JsonException $e) {
            throw new JsonException("Failed to encode package details: {$e->getMessage()}");
        }
    }

    /**
     * Create a new instance from an array
     *
     * @throws InvalidArgumentException
     */
    public static function fromArray(array $data): self
    {
        $requiredKeys = ['minified', 'packages', 'security-advisories'];

        // Validate required keys
        foreach ($requiredKeys as $key) {
            if (!array_key_exists($key, $data)) {
                throw new InvalidArgumentException("Missing required key: {$key}");
            }
        }

        // Validate data types
        if (!is_string($data['minified'])) {
            throw new InvalidArgumentException('Minified value must be a string');
        }

        if (!is_array($data['packages'])) {
            throw new InvalidArgumentException('Packages must be an array');
        }

        if (!is_array($data['security-advisories'])) {
            throw new InvalidArgumentException('Security advisories must be an array');
        }

        return new self(
            $data['minified'],
            $data['packages'],
            $data['security-advisories'],
            $data['last_modified'] ?? null,
        );
    }

    /**
     * Create a new instance from JSON
     *
     * @throws JsonException
     * @throws InvalidArgumentException
     */
    public static function fromJson(string $json): self
    {
        try {
            $data = json_decode($json, true, 512, JSON_THROW_ON_ERROR);
            return self::fromArray($data);
        } catch (JsonException $e) {
            throw new JsonException("Invalid JSON format: {$e->getMessage()}");
        }
    }
}
