<?php declare(strict_types=1);

namespace App\DataProvider;

class EntityPatch
{
    public const ADD_POINT = 'add.point';
    public const EDIT_POINT = 'edit.point';
    public const DELETE_POINT = 'delete.point';
    public const DONE_POINT = 'done.point';
    public const UNDONE_POINT = 'undone.point';

    public const TODO_TASK_ID = [
        self::EDIT_POINT,
        self::DELETE_POINT,
        self::DONE_POINT,
        self::UNDONE_POINT,
    ];

    public const TODO_TASK_TITLE = [
        self::ADD_POINT,
        self::EDIT_POINT,
    ];
}
