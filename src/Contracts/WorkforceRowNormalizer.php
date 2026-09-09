<?php

declare(strict_types=1);

namespace Rimba\Sync\Contracts;

interface WorkforceRowNormalizer
{
    public function normalize(array $row): array;
}
