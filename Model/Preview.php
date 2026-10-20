<?php

declare(strict_types=1);

namespace Stoneholl\LatestInternalComment\Model;

/** Formats a short preview from already-authorized, size-bounded text. */
class Preview
{
    public function format(string $prefix): array
    {
        $text = mb_substr($prefix, 0, 200, 'UTF-8');
        return [
            'text' => $text,
            'truncated' => mb_strlen($prefix, 'UTF-8') > 200,
            'unavailable' => trim($text, " \t\r\n") === ''
        ];
    }
}
