<?php declare(strict_types=1);

namespace App\Entity\ToDo;

use App\Repository\ToDo\TaskRepository;
use App\Utils\DateTimeUtils;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\Criteria;
use Doctrine\ORM\Mapping as ORM;
use Ramsey\Uuid\Uuid;

#[ORM\Entity(repositoryClass: TaskRepository::class)]
#[ORM\Table(name: 'todo__tasks')]
#[ORM\HasLifecycleCallbacks]
class Task
{
    #[ORM\Id]
    #[ORM\Column(name: 'id', type: 'string')]
    private string $id;

    #[ORM\Column(name: 'title', type: 'string', length: 255)]
    private string $title;

    #[ORM\Column(name: 'priority', type: 'integer')]
    private int $priority;

    #[ORM\OneToMany(mappedBy: 'task', targetEntity: Point::class, cascade: ['persist', 'remove'])]
    private Collection $points;

    #[ORM\Column(name: 'created_at', type: 'datetime_immutable')]
    private \DateTimeImmutable $createdAt;

    #[ORM\Column(name: 'updated_at', type: 'datetime_immutable')]
    private \DateTimeImmutable $updatedAt;

    /**
     * @param string $title
     * @param int $priority
     */
    public function __construct(string $title, int $priority)
    {
        $this->id = Uuid::uuid4()->toString();
        $this->title = $title;
        $this->priority = $priority;
        $this->points = new ArrayCollection();

        $now = DateTimeUtils::now();
        $this->createdAt = $now;
        $this->updatedAt = $now;
    }

    /**
     * @return string
     */
    public function getId(): string
    {
        return $this->id;
    }

    /**
     * @param string $title
     */
    public function setTitle(string $title): void
    {
        $this->title = $title;
    }

    /**
     * @return string
     */
    public function getTitle(): string
    {
        return $this->title;
    }

    /**
     * @param \DateTimeImmutable $createdAt
     */
    public function setCreatedAt(\DateTimeImmutable $createdAt): void
    {
        $this->createdAt = $createdAt;
    }

    /**
     * @return \DateTimeImmutable
     */
    public function getCreatedAt(): \DateTimeImmutable
    {
        return $this->createdAt;
    }

    /**
     * @param \DateTimeImmutable $updatedAt
     */
    public function setUpdatedAt(\DateTimeImmutable $updatedAt): void
    {
        $this->updatedAt = $updatedAt;
    }

    /**
     * @return \DateTimeImmutable
     */
    public function getUpdatedAt(): \DateTimeImmutable
    {
        return $this->updatedAt;
    }

    /**
     * @param int $priority
     */
    public function setPriority(int $priority): void
    {
        $this->priority = $priority;
    }

    /**
     * @return int
     */
    public function getPriority(): int
    {
        return $this->priority;
    }

    /**
     * @return Collection
     */
    public function getPoints(): Collection
    {
        $criteria = Criteria::create();
        $criteria->orderBy(['title' => 'ASC']);

        return $this->points->matching($criteria);
    }

    /**
     * @return string
     */
    public function getCompletedPercent(): string
    {
        $allCount = $this->points->count();
        if ($allCount === 0) {
            return '0';
        }

        $criteria = Criteria::create();
        $criteria->andWhere(Criteria::expr()->eq('completed', true));
        $completed = $this->points->matching($criteria);
        $completedCount = $completed->count();

        if ($completedCount === $allCount) {
            return '100%';
        }

        $percent = (floatval($completedCount) / floatval($allCount)) * 100;

        return number_format($percent, 2, '.', '') . '%';
    }

    /**
     * @param Point $point
     */
    public function addPoint(Point $point): void
    {
        if (!$this->points->contains($point)) {
            $this->points->add($point);
            $point->setTask($this);
        }
    }

    /**
     * @param Point $point
     */
    public function removePoint(Point $point): void
    {
        if ($this->points->contains($point)) {
            $this->points->removeElement($point);
            $point->setTask(null);
        }
    }

    #[ORM\PreUpdate]
    public function preUpdate(): void
    {
        $this->updatedAt = DateTimeUtils::now();
    }
}
