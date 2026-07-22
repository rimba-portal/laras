<?php

declare(strict_types=1);

namespace Rimba\Sync\Contracts;

interface DataFetcher
{
    public function fetch(array $config): array;
}
