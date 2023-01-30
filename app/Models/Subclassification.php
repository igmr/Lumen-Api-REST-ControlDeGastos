<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Subclassification extends Model
{
    //* La acción de eliminar registro se realizara por medio del campo deleted_at.
    use SoftDeletes;
    //* Definir tabla.
    protected $table = "subclassifications";
    //* Definir clave primaria.
    protected $primaryKey = "id";
    //* Campos visibles en consulta.
    protected $fillable = [
        "classification_id",
        "name",
        "description",
        "icon",
    ];
    //* Campos no visibles en consulta.
    protected $hidden = [
        "created_at",
        "updated_at",
        "deleted_at",
    ];
}