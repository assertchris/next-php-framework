<?php

declare(strict_types=1);

namespace Next\Http;

final class UnsupportedContentType extends \RuntimeException
{
    public static function forFileType(string $fileType): self
    {
        return new self("File extension {$fileType} not supported");
    }
}
