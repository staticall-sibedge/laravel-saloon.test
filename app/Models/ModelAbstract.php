<?php declare(strict_types=1);

namespace App\Models;

use App\Repository\RepositoryInterface;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Support\Str;

#[Hidden([ModelAbstract::COLUMN_CREATED_AT, ModelAbstract::COLUMN_UPDATED_AT, ModelAbstract::COLUMN_DELETED_AT])]
abstract class ModelAbstract extends Model
{
    public const string COLUMN_ID = 'id';
    public const string COLUMN_CREATED_AT = 'created_at';
    public const string COLUMN_UPDATED_AT = 'updated_at';
    public const string COLUMN_DELETED_AT = 'deleted_at';

    public function getAttribute($key)
    {
        return parent::getAttribute(Str::snake($key));
    }

    public function setAttribute($key, $value)
    {
        return parent::setAttribute(Str::snake($key), $value);
    }

    abstract public static function getRepository(): RepositoryInterface;
}
