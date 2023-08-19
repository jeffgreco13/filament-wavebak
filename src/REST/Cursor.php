<?php

namespace Jeffgreco13\FilamentWave\REST;

use Iterator;
use RuntimeException;
use Illuminate\Support\Collection;
use Jeffgreco13\FilamentWave\FilamentWave;

class Cursor implements Iterator
{
    protected array $results = [];
    protected int $position = 0;
    protected array $pageInfo = [];
    protected $filamentWave;

    public function __construct(Collection $results, array $pageInfo, FilamentWave $filamentWave)
    {
        $this->results[$this->position] = $results;
        $this->pageInfo = $pageInfo;
        $this->filamentWave = $filamentWave;
    }

    public function current(): Collection
    {
        return $this->results[$this->position];
    }

    public function hasNext(): bool
    {
        return $this->pageInfo['currentPage'] < $this->pageInfo['totalPages'];
    }

    public function hasPrev(): bool
    {
        return $this->pageInfo['currentPage'] > 1;
    }

    public function key(): int
    {
        return $this->position;
    }

    public function next(): void
    {
        $this->position++;

        if (!$this->valid() && $this->hasNext()) {
            $this->results[$this->position] = $this->fetchNextResults();
        }
    }

    public function prev(): void
    {
        if (!$this->hasPrev()) {
            throw new RuntimeException('No previous results available.');
        }

        $this->position--;
    }

    public function rewind(): void
    {
        $this->position = 0;
    }

    public function valid(): bool
    {
        return isset($this->results[$this->position]);
    }

    protected function fetchNextResults()
    {
        $method = $this->filamentWave->getCachedMethod();
        $data = $this->filamentWave->nextPage()->{$method}();
        $this->pageInfo = $data['pageInfo'];
        return $data['records'];
    }

}
