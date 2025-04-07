<?php

declare(strict_types=1);

namespace Atoolo\Microsite\Environment;

use Atoolo\Resource\Resource;
use InvalidArgumentException;

class MicrositeContext
{
    /**
     * @param array<string> $mountableObjectTypes
     */
    public function __construct(
        public readonly string $resourceDir,
        public readonly ?string $currentPath,
        public readonly string $micrositePath,
        public readonly string $mainHost,
        public readonly int $siteId,
        public readonly array $mountableObjectTypes,
    ) {
        if (str_ends_with($micrositePath, '/')) {
            throw new InvalidArgumentException('Microsite path must not end with a slash');
        }
    }

    public function isMicrositePath(?string $path): bool
    {
        return $path !== null && str_starts_with($path, $this->micrositePath);
    }
}
