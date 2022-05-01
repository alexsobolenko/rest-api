<?php declare(strict_types=1);

namespace App\Entity\ToDo;

use App\Repository\ToDo\PointRepository;
use App\Utils\DateTimeUtils;
use Doctrine\ORM\Mapping as ORM;
use Ramsey\Uuid\Uuid;

#[ORM\Entity(repositoryClass: PointRepository::class)]
#[ORM\Table(name: 'todo__points')]
#[ORM\HasLifecycleCallbacks]
class Point
{
    #[ORM\Id]
    #[ORM\Column(name: 'id', type: 'string')]
    private string $id;

    #[ORM\Column(name: 'title', type: 'string', length: 255)]
    private string $title;

    #[ORM\Column(name: 'completed', type: 'boolean')]
    private bool $completed;

    #[ORM\ManyToOne(targetEntity: Task::class, inversedBy: 'points')]
    #[ORM\JoinColumn(name: 'task_id', onDelete: 'CASCADE')]
    private Task $task;

    #[ORM\Column(name: 'created_at', type: 'datetime_immutable')]
    private \DateTimeImmutable $createdAt;

    #[ORM\Column(name: 'updated_at', type: 'datetime_immutable')]
    private \DateTimeImmutable $updatedAt;

    /**
     * @param Task $task
     * @param string $title
     */
    public function __construct(Task $task, string $title)
    {
        $this->id = Uuid::uuid4()->toString();
        $this->task = $task;
        $this->title = $title;
        $this->completed = false;

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
     * @return string
     */
    public function getTitle(): string
    {
        return $this->title;
    }

    /**
     * @param string $title
     */
    public function setTitle(string $title): void
    {
        $this->title = $title;
    }

    /**
     * @return \DateTimeImmutable
     */
    public function getCreatedAt(): \DateTimeImmutable
    {
        return $this->createdAt;
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
    public function getUpdatedAt(): \DateTimeImmutable
    {
        return $this->updatedAt;
    }

    /**
     * @param \DateTimeImmutable $updatedAt
     */
    public function setUpdatedAt(\DateTimeImmutable $updatedAt): void
    {
        $this->updatedAt = $updatedAt;
    }

    /**
     * @return bool
     */
    public function isCompleted(): bool
    {
        return $this->completed;
    }

    /**
     * @param bool $completed
     */
    public function setCompleted(bool $completed): void
    {
        $this->completed = $completed;
    }

    /**
     * @return Task
     */
    public function getTask(): Task
    {
        return $this->task;
    }

    /**
     * @param Task $task
     */
    public function setTask(Task $task): void
    {
        $this->task = $task;
    }

    #[ORM\PreUpdate]
    public function preUpdate(): void
    {
        $this->updatedAt = DateTimeUtils::now();
    }
}
