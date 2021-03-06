<?php

declare(strict_types=1);

namespace EMS\CoreBundle\Form\DataTransformer;

use Symfony\Component\Form\DataTransformerInterface;

final class ChannelOptionsTransformer implements DataTransformerInterface
{
    public function transform($value)
    {
        $searchConfig = $this->jsonFormat($value, 'searchConfig');
        $attributes = $this->jsonFormat($value, 'attributes');

        return [
            'searchConfig' => $searchConfig,
            'entryRoute' => $value['entryRoute'] ?? null,
            'attributes' => $attributes,
        ];
    }

    public function reverseTransform($value)
    {
        return [
            'searchConfig' => $value['searchConfig'] ?? '',
            'entryRoute' => $value['entryRoute'] ?? '',
            'attributes' => $value['attributes'] ?? '',
        ];
    }

    /**
     * @param array<string, mixed> $value
     */
    private function jsonFormat(array $value, string $attribute): string
    {
        $formatted = \json_decode($value[$attribute] ?? '', true);
        if (null === $formatted) {
            $formatted = $value[$attribute] ?? '';
        } else {
            $formatted = \json_encode($formatted ?? '', JSON_PRETTY_PRINT);
        }

        return $formatted;
    }
}
