<?php

declare(strict_types=1);

namespace Rfuehricht\Recordmodules\Event;


/**
 * This event triggers right before the PIDs for a module listing are fetched.
 *
 * Listeners may provide custom PIDs. EXT:recordmodules will NOT use the default PIDs if a listener provides PIDs.
 */
final class BeforePidsLoadedEvent
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
