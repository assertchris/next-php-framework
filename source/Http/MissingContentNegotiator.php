<?php

declare(strict_types=1);

namespace Next\Http;

final class MissingContentNegotiator extends \RuntimeException
{
    public static function forMethod(string $method): MissingContentNegotiator
    {
        return new self("No content negotiator for {$method}");
    }
}
