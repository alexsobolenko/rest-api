<?php declare(strict_types=1);

namespace App\Model;

use App\Exception\RestApiException;

class PaginatedDataModel
{
    public int $total;
    public int $pages;
    public int $limit;
    public int $page;
    public array $items;
    public array $pageItems;

    /**
     * @param int $total
     * @param int $limit
     * @param int $page
     * @param array $items
     */
    public function __construct(int $total, int $limit, int $page, array $items)
    {
        $this->pageItems = [1];
        $this->total = $total;
        $this->limit = $limit;
        $this->page = $page;
        $this->items = $items;
        $this->pages = 0;

        try {
            if ($this->total > 0) {
                $this->pages = (int) ceil($this->total / $this->limit);
                if ($this->pages < 5) {
                    for ($i = 2; $i <= $this->pages; $i++) {
                        $this->pageItems[] = $i;
                    }
                } else {
                    if ($this->page >= 5) {
                        $this->pageItems[] = '...';
                    }

                    if ($this->page + 4 <= $this->pages && $this->page >= 5) {
                        for ($i = $this->page - 2; $i <= $this->page + 2; $i++) {
                            $this->pageItems[] = $i;
                        }
                    } else {
                        if ($this->page > 5) {
                            $start = $this->pages - 4;
                            $end = $this->pages;
                        } else {
                            $start = 2;
                            $end = 5;
                        }

                        if ($start < 2) {
                            $start = 2;
                        }

                        if ($end > $this->pages) {
                            $end = $this->pages;
                        }

                        for ($i = $start; $i <= $end; $i++) {
                            $this->pageItems[] = $i;
                        }
                    }

                    if ($this->page <= $this->pages - 4) {
                        $this->pageItems[] = '...';
                    }

                    if (!in_array($this->pages, $this->pageItems)) {
                        $this->pageItems[] = $this->pages;
                    }
                }
            }
        } catch (\Throwable $e) {
            throw RestApiException::string($e->getMessage());
        }
    }
}
