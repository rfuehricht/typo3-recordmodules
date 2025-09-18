<?php

declare(strict_types=1);

namespace Rfuehricht\Recordmodules\Event;


/**
 * This event triggers right after the PIDs for a module listing were fetched.
 *
 * Listeners may manipulate the PIDs.
 */
final class AfterPidsLoadedEvent
{

    public function __construct(
        private array           $pids,
        private readonly string $table
    )
    {
    }

    public function getPids(): array
    {
        return $this->pids;
    }

    public function setPids(array $pids): void
    {
        $this->pids = $pids;
    }

    public function getTable(): string
    {
        return $this->table;
    }

}
