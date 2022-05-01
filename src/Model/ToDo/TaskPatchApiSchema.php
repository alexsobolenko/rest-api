<?php declare(strict_types=1);

namespace App\Model\ToDo;

use App\DataProvider\EntityPatch;
use App\Model\AbstractApiSchema;
use Symfony\Component\Validator\Constraints as Assert;

class TaskPatchApiSchema extends AbstractApiSchema
{
    #[Assert\NotBlank(message: 'Point id should not be blank', groups: EntityPatch::TODO_TASK_ID)]
    public ?string $pointId = null;

    #[Assert\NotBlank(message: 'Title should not be blank', groups: EntityPatch::TODO_TASK_TITLE)]
    #[Assert\Length(
        min: 3,
        max: 250,
        minMessage: 'Title min length is 3',
        maxMessage: 'Title max length is 250',
        groups: EntityPatch::TODO_TASK_TITLE,
    )]
    public ?string $title = null;
}
