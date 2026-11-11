<?php
function capitalizeInput(string $value): string {
    $value = trim($value);

    return preg_replace_callback('/[\p{L}\p{N}]+(?:[\'’][\p{L}\p{N}]+)*/u', function (array $matches): string {
        $word = $matches[0];
        if (mb_strtoupper($word, 'UTF-8') === $word) {
            return $word;
        }

        return mb_strtoupper(mb_substr($word, 0, 1, 'UTF-8'), 'UTF-8')
            . mb_strtolower(mb_substr($word, 1, null, 'UTF-8'), 'UTF-8');
    }, $value) ?? $value;
}
