<?php declare(strict_types=1);

namespace App\Model\ToDo;

use App\Model\AbstractApiSchema;
use Symfony\Component\Validator\Constraints as Assert;

class TaskPostApiSchema extends AbstractApiSchema
{
    #[Assert\NotBlank(message: 'Title should not be blank')]
    #[Assert\Length(min: 3, max: 250, minMessage: 'Title min length is 3', maxMessage: 'Title max length is 250')]
    public ?string $title = null;

    #[Assert\NotBlank(message: 'Priority should not be blank')]
    #[Assert\Range(notInRangeMessage: 'Priority should be between 0 and 10', min: 0, max: 10)]
    public ?int $priority = null;
}
