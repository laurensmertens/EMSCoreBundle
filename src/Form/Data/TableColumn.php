<?php

declare(strict_types=1);

namespace EMS\CoreBundle\Form\Data;

final class TableColumn
{
    private string $titleKey;
    private string $attribute;
    /** @var array<mixed, string> */
    private array $valueToIconMapping;
    private ?string $routeProperty = null;
    private ?string $routeTarget = '_blank';
    private ?string $iconProperty = null;

    /**
     * @param array<mixed, string> $valueToIconMapping
     */
    public function __construct(string $titleKey, string $attribute, array $valueToIconMapping = [])
    {
        $this->titleKey = $titleKey;
        $this->attribute = $attribute;
        $this->valueToIconMapping = $valueToIconMapping;
    }

    public function getTitleKey(): string
    {
        return $this->titleKey;
    }

    public function getAttribute(): string
    {
        return $this->attribute;
    }

    /**
     * @return array<mixed, string>
     */
    public function getValueToIconMapping(): array
    {
        return $this->valueToIconMapping;
    }

    public function setRouteProperty(string $routeProperty): void
    {
        $this->routeProperty = $routeProperty;
    }

    public function getRouteProperty(): ?string
    {
        return $this->routeProperty;
    }

    public function setRouteTarget(?string $target): ?string
    {
        return $this->routeTarget = $target;
    }

    public function getRouteTarget(): ?string
    {
        return $this->routeTarget;
    }

    public function getIconProperty(): ?string
    {
        return $this->iconProperty;
    }

    public function setIconProperty(?string $iconProperty): void
    {
        $this->iconProperty = $iconProperty;
    }
}
